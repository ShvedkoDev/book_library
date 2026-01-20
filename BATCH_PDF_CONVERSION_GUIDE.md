# Batch PDF Conversion Guide
## Remove Object Streams (PDF 1.5+) While Preserving Compression

This guide helps you convert PDFs with Object Streams to FPDI-compatible format without significantly increasing file size.

---

## 📊 **Why Convert?**

- **Current situation:** ~50% of your PDFs use Object Streams (PDF 1.5+)
- **Problem:** Free FPDI parser cannot read these PDFs
- **Solution:** Convert to PDF 1.4 format (removes Object Streams only)
- **Result:** Cover page merging works for all PDFs

---

## 🎯 **File Size Impact**

| Method | Structure | Content Compression | Size Increase |
|--------|-----------|---------------------|---------------|
| **QPDF** ⭐ | Decompressed | **Preserved** | ~2-8% |
| **Ghostscript** | Decompressed | Re-compressed | ~10-20% |

**Example for 750 PDFs (50% of 1,500):**
- Current total: ~2GB
- After QPDF: ~2.12GB (+120MB)
- After Ghostscript: ~2.3GB (+300MB)

---

## 🚀 **Method 1: QPDF (Recommended)**

### Installation

**Linux/Ubuntu:**
```bash
sudo apt-get install qpdf
```

**Mac:**
```bash
brew install qpdf
```

**Windows:**
1. Download from: http://qpdf.sourceforge.net/
2. Extract to `C:\qpdf\`
3. Add to PATH or place `qpdf.exe` with PDFs

### Single File Conversion
```bash
qpdf --object-streams=disable input.pdf output.pdf
```

### Batch Conversion

**Linux/Mac:**
```bash
# Make script executable
chmod +x convert_pdfs_batch.sh

# Run conversion
./convert_pdfs_batch.sh
```

**Windows:**
```
Double-click: convert_pdfs_batch.bat
```

**Output:**
- Converted PDFs → `./converted/` folder
- Log file → `conversion_log.txt`

---

## 🔧 **Method 2: Ghostscript (Alternative)**

### Installation

**Linux/Ubuntu:**
```bash
sudo apt-get install ghostscript
```

**Mac:**
```bash
brew install ghostscript
```

**Windows:**
Download from: https://www.ghostscript.com/releases/gsdnld.html

### Single File Conversion
```bash
gs -sDEVICE=pdfwrite \
   -dCompatibilityLevel=1.4 \
   -dPDFSETTINGS=/prepress \
   -dAutoFilterColorImages=false \
   -dAutoFilterGrayImages=false \
   -dColorImageFilter=/FlateEncode \
   -dGrayImageFilter=/FlateEncode \
   -dNOPAUSE -dQUIET -dBATCH \
   -sOutputFile=output.pdf \
   input.pdf
```

### Batch Conversion
Edit `convert_pdfs_batch.sh` and change:
```bash
METHOD="ghostscript"  # Change from "qpdf"
```

Then run as normal.

---

## 📋 **Step-by-Step Workflow**

### 1. Export Object Streams List
1. Visit `/admin/pdf-compression-check`
2. Click **"Export Object Streams List (PDF 1.5+)"**
3. Save CSV file

### 2. Download PDFs from Server

**Option A: Via SFTP/FTP**
```bash
# Using scp (replace with your details)
scp -r user@server:/path/to/storage/app/public/books ./books
```

**Option B: Via Admin Panel**
- Manually download files listed in CSV

**Option C: Server-side Script** (if you have SSH access)
```bash
# On server, create archive of only problematic PDFs
cd storage/app/public/books
cat /path/to/object-streams-list.csv | tail -n +2 | cut -d',' -f5 | \
  xargs tar -czf ~/object-streams-pdfs.tar.gz
```

### 3. Convert Locally
```bash
cd /path/to/downloaded/pdfs
chmod +x convert_pdfs_batch.sh
./convert_pdfs_batch.sh
```

### 4. Upload Converted PDFs
Replace original files with converted versions from `./converted/` folder.

**Option A: Via SFTP/FTP**
```bash
scp -r ./converted/* user@server:/path/to/storage/app/public/books/
```

**Option B: Via Admin Panel**
- Delete original files
- Upload converted files

### 5. Verify on Server
1. Visit `/admin/pdf-compression-check`
2. Click **"Clear Cache & Recheck All"**
3. Verify Object Streams count reduced to 0

---

## 🧪 **Test First!**

Before batch converting, test with a few files:

```bash
# Test QPDF on 3 files
for i in {1..3}; do
  pdf=$(ls *.pdf | head -n $i | tail -n 1)
  qpdf --object-streams=disable "$pdf" "test_$pdf"
done

# Compare sizes
ls -lh *.pdf test_*.pdf
```

---

## ⚠️ **Important Notes**

### What Changes:
- ✅ PDF version: 1.5/1.6 → 1.4
- ✅ Object Streams: Removed
- ✅ XRef Streams: Removed (if present)

### What Stays The Same:
- ✅ Content compression (FlateDecode, DCTDecode)
- ✅ Image quality
- ✅ Text content
- ✅ Fonts
- ✅ Metadata
- ✅ Page count

### Don't Convert These:
- ❌ Already compatible PDFs (status: "Normal")
- ❌ Encrypted/password-protected PDFs
- ❌ Corrupted PDFs

---

## 🆘 **Troubleshooting**

### "qpdf: command not found"
- Install QPDF (see installation section)
- On Windows: Add to PATH or copy `qpdf.exe` to script directory

### "Permission denied"
```bash
chmod +x convert_pdfs_batch.sh
```

### "Output file already exists"
- Delete files in `./converted/` folder
- Or change output directory in script

### File size increased too much
- Check you're using QPDF (not Ghostscript)
- Some PDFs may have poorly compressed content to begin with
- Typically <10% increase is normal

### Conversion failed for specific PDF
- Check `conversion_log.txt` for details
- File may be corrupted or encrypted
- Skip that file and process manually

---

## 💰 **Cost-Benefit Analysis**

### Option 1: Batch Convert (~750 PDFs)
**Pros:**
- ✅ Free
- ✅ One-time effort
- ✅ Works forever
- ✅ Minimal size increase with QPDF

**Cons:**
- ❌ Manual work (download/convert/upload)
- ❌ ~2-4 hours of work
- ❌ Need to repeat for new uploads

**Estimated time:** 2-4 hours

### Option 2: Purchase Paid Parser (~€150-200)
**Pros:**
- ✅ Automatic (no manual work)
- ✅ Works with future uploads
- ✅ Handles encrypted PDFs too
- ✅ One-time cost

**Cons:**
- ❌ ~€150-200 cost
- ❌ Per-site license

**Cost per PDF:** ~€0.20 (for 750 PDFs)

### Option 3: Do Nothing (Current)
**Pros:**
- ✅ Zero work
- ✅ Free
- ✅ Users still get PDFs (without cover)

**Cons:**
- ❌ No cover pages on 50% of PDFs
- ❌ Less professional presentation

---

## 📞 **Support**

- **QPDF Documentation:** http://qpdf.sourceforge.net/files/qpdf-manual.html
- **Ghostscript Docs:** https://www.ghostscript.com/doc/current/Use.htm
- **Script Issues:** Check `conversion_log.txt`

---

## ✅ **Quick Reference Commands**

```bash
# Single file (QPDF)
qpdf --object-streams=disable input.pdf output.pdf

# Single file (Ghostscript)
gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 \
   -dPDFSETTINGS=/prepress -dNOPAUSE -dQUIET -dBATCH \
   -sOutputFile=output.pdf input.pdf

# Batch (Linux/Mac)
./convert_pdfs_batch.sh

# Batch (Windows)
convert_pdfs_batch.bat

# Check PDF version
grep -a "PDF-" yourfile.pdf | head -n 1
```

---

**Last Updated:** 2026-01-20
**For:** FSM National VLA Curriculum Digital Library
