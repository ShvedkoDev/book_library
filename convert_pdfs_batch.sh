#!/bin/bash
#
# Batch PDF Converter - Remove Object Streams (PDF 1.5+) while preserving compression
#
# This script converts PDF 1.5/1.6 files to PDF 1.4 format by removing Object Streams,
# which allows them to work with the free FPDI parser for cover page merging.
#
# Requirements:
#   - qpdf (recommended) OR ghostscript
#   - bash
#
# Installation:
#   Ubuntu/Debian: sudo apt-get install qpdf
#   Mac:           brew install qpdf
#   Windows:       Download from http://qpdf.sourceforge.net/
#
# Usage:
#   1. Place this script in a directory with your PDF files
#   2. Make executable: chmod +x convert_pdfs_batch.sh
#   3. Run: ./convert_pdfs_batch.sh
#   4. Converted files will be in ./converted/ subdirectory
#
# File size impact:
#   - QPDF method: ~2-8% increase (structure only)
#   - Ghostscript method: ~10-20% increase (recompression)

# Configuration
METHOD="qpdf"  # Options: "qpdf" or "ghostscript"
INPUT_DIR="."
OUTPUT_DIR="./converted"
LOG_FILE="conversion_log.txt"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Create output directory
mkdir -p "$OUTPUT_DIR"

# Initialize log
echo "PDF Conversion Started: $(date)" > "$LOG_FILE"
echo "Method: $METHOD" >> "$LOG_FILE"
echo "----------------------------------------" >> "$LOG_FILE"

# Check if tools are available
if [ "$METHOD" = "qpdf" ]; then
    if ! command -v qpdf &> /dev/null; then
        echo -e "${RED}ERROR: qpdf not found!${NC}"
        echo "Please install: sudo apt-get install qpdf (Ubuntu/Debian) or brew install qpdf (Mac)"
        exit 1
    fi
    echo -e "${GREEN}Using QPDF method (preserves compression, minimal size increase)${NC}"
elif [ "$METHOD" = "ghostscript" ]; then
    if ! command -v gs &> /dev/null; then
        echo -e "${RED}ERROR: ghostscript not found!${NC}"
        echo "Please install: sudo apt-get install ghostscript (Ubuntu/Debian) or brew install ghostscript (Mac)"
        exit 1
    fi
    echo -e "${YELLOW}Using Ghostscript method (may increase file size 10-20%)${NC}"
fi

# Statistics
TOTAL=0
SUCCESS=0
FAILED=0
SKIPPED=0
TOTAL_SIZE_BEFORE=0
TOTAL_SIZE_AFTER=0

echo -e "\n${BLUE}Scanning for PDF files...${NC}"

# Process all PDFs in current directory
for pdf in "$INPUT_DIR"/*.pdf; do
    # Skip if no PDFs found
    [ -e "$pdf" ] || continue

    TOTAL=$((TOTAL + 1))
    FILENAME=$(basename "$pdf")
    OUTPUT_FILE="$OUTPUT_DIR/$FILENAME"

    # Skip if already converted
    if [ -f "$OUTPUT_FILE" ]; then
        echo -e "${YELLOW}⊘ Skipping (already exists): $FILENAME${NC}"
        SKIPPED=$((SKIPPED + 1))
        continue
    fi

    # Get original file size
    SIZE_BEFORE=$(stat -f%z "$pdf" 2>/dev/null || stat -c%s "$pdf" 2>/dev/null)
    TOTAL_SIZE_BEFORE=$((TOTAL_SIZE_BEFORE + SIZE_BEFORE))

    echo -e "\n${BLUE}Processing: $FILENAME${NC}"
    echo "  Original size: $(numfmt --to=iec-i --suffix=B $SIZE_BEFORE 2>/dev/null || echo "$SIZE_BEFORE bytes")"

    # Convert based on method
    if [ "$METHOD" = "qpdf" ]; then
        # QPDF method - fast, minimal size increase
        if qpdf --object-streams=disable "$pdf" "$OUTPUT_FILE" 2>&1 | tee -a "$LOG_FILE"; then
            SUCCESS=$((SUCCESS + 1))
            SIZE_AFTER=$(stat -f%z "$OUTPUT_FILE" 2>/dev/null || stat -c%s "$OUTPUT_FILE" 2>/dev/null)
            TOTAL_SIZE_AFTER=$((TOTAL_SIZE_AFTER + SIZE_AFTER))
            INCREASE=$((SIZE_AFTER - SIZE_BEFORE))
            PERCENT=$(awk "BEGIN {printf \"%.1f\", ($INCREASE / $SIZE_BEFORE) * 100}")

            echo -e "  ${GREEN}✓ Success${NC}"
            echo "  New size: $(numfmt --to=iec-i --suffix=B $SIZE_AFTER 2>/dev/null || echo "$SIZE_AFTER bytes")"
            echo "  Change: +${PERCENT}%"
            echo "[SUCCESS] $FILENAME | Size: $SIZE_BEFORE -> $SIZE_AFTER (+${PERCENT}%)" >> "$LOG_FILE"
        else
            FAILED=$((FAILED + 1))
            echo -e "  ${RED}✗ Failed${NC}"
            echo "[FAILED] $FILENAME | Error logged above" >> "$LOG_FILE"
        fi

    elif [ "$METHOD" = "ghostscript" ]; then
        # Ghostscript method - slower, larger size increase
        if gs -sDEVICE=pdfwrite \
              -dCompatibilityLevel=1.4 \
              -dPDFSETTINGS=/prepress \
              -dAutoFilterColorImages=false \
              -dAutoFilterGrayImages=false \
              -dColorImageFilter=/FlateEncode \
              -dGrayImageFilter=/FlateEncode \
              -dNOPAUSE -dQUIET -dBATCH \
              -sOutputFile="$OUTPUT_FILE" \
              "$pdf" 2>&1 | tee -a "$LOG_FILE"; then
            SUCCESS=$((SUCCESS + 1))
            SIZE_AFTER=$(stat -f%z "$OUTPUT_FILE" 2>/dev/null || stat -c%s "$OUTPUT_FILE" 2>/dev/null)
            TOTAL_SIZE_AFTER=$((TOTAL_SIZE_AFTER + SIZE_AFTER))
            INCREASE=$((SIZE_AFTER - SIZE_BEFORE))
            PERCENT=$(awk "BEGIN {printf \"%.1f\", ($INCREASE / $SIZE_BEFORE) * 100}")

            echo -e "  ${GREEN}✓ Success${NC}"
            echo "  New size: $(numfmt --to=iec-i --suffix=B $SIZE_AFTER 2>/dev/null || echo "$SIZE_AFTER bytes")"
            echo "  Change: +${PERCENT}%"
            echo "[SUCCESS] $FILENAME | Size: $SIZE_BEFORE -> $SIZE_AFTER (+${PERCENT}%)" >> "$LOG_FILE"
        else
            FAILED=$((FAILED + 1))
            echo -e "  ${RED}✗ Failed${NC}"
            echo "[FAILED] $FILENAME | Error logged above" >> "$LOG_FILE"
        fi
    fi
done

# Summary
echo -e "\n${BLUE}========================================${NC}"
echo -e "${BLUE}         CONVERSION SUMMARY${NC}"
echo -e "${BLUE}========================================${NC}"
echo "Total PDFs found:    $TOTAL"
echo -e "${GREEN}Successfully converted: $SUCCESS${NC}"
echo -e "${RED}Failed:                 $FAILED${NC}"
echo -e "${YELLOW}Skipped (exist):        $SKIPPED${NC}"

if [ $SUCCESS -gt 0 ]; then
    echo ""
    echo "Storage Impact:"
    echo "  Before: $(numfmt --to=iec-i --suffix=B $TOTAL_SIZE_BEFORE 2>/dev/null || echo "$TOTAL_SIZE_BEFORE bytes")"
    echo "  After:  $(numfmt --to=iec-i --suffix=B $TOTAL_SIZE_AFTER 2>/dev/null || echo "$TOTAL_SIZE_AFTER bytes")"
    TOTAL_INCREASE=$((TOTAL_SIZE_AFTER - TOTAL_SIZE_BEFORE))
    TOTAL_PERCENT=$(awk "BEGIN {printf \"%.1f\", ($TOTAL_INCREASE / $TOTAL_SIZE_BEFORE) * 100}")
    echo "  Increase: $(numfmt --to=iec-i --suffix=B $TOTAL_INCREASE 2>/dev/null || echo "$TOTAL_INCREASE bytes") (+${TOTAL_PERCENT}%)"
fi

echo ""
echo "Converted files saved to: $OUTPUT_DIR/"
echo "Detailed log saved to: $LOG_FILE"
echo ""
echo -e "${BLUE}========================================${NC}"

# Add summary to log
echo "" >> "$LOG_FILE"
echo "========================================" >> "$LOG_FILE"
echo "SUMMARY" >> "$LOG_FILE"
echo "========================================" >> "$LOG_FILE"
echo "Total: $TOTAL | Success: $SUCCESS | Failed: $FAILED | Skipped: $SKIPPED" >> "$LOG_FILE"
echo "Size increase: +${TOTAL_PERCENT}%" >> "$LOG_FILE"
echo "Completed: $(date)" >> "$LOG_FILE"

# Exit with appropriate code
if [ $FAILED -gt 0 ]; then
    exit 1
else
    exit 0
fi
