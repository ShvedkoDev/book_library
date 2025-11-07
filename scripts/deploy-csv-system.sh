#!/bin/bash

################################################################################
# CSV Import/Export System Deployment Script
#
# This script automates the deployment checklist tasks for the CSV import/export
# system as documented in CSV_IMPORT_TODO.md section 12.3.
#
# Usage:
#   ./scripts/deploy-csv-system.sh [options]
#
# Options:
#   --dry-run       Show what would be done without making changes
#   --verbose       Show detailed output
#   --skip-perms    Skip permission changes
#   --docker        Run migrations in Docker container (default: auto-detect)
#   --help          Show this help message
#
# Exit Codes:
#   0 - Success
#   1 - General error
#   2 - Prerequisites not met
#   3 - Migration failed
#   4 - Directory creation failed
#   5 - Permission change failed
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
DRY_RUN=false
VERBOSE=false
SKIP_PERMS=false
USE_DOCKER=""
LOG_FILE="$PROJECT_DIR/storage/logs/csv-deploy-$(date +%Y%m%d-%H%M%S).log"

# Counters
TASKS_TOTAL=0
TASKS_COMPLETED=0
TASKS_SKIPPED=0
TASKS_FAILED=0

################################################################################
# Helper Functions
################################################################################

log() {
    local level=$1
    shift
    local message="$@"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')

    echo "[$timestamp] [$level] $message" >> "$LOG_FILE"

    case $level in
        INFO)
            echo -e "${BLUE}ℹ ${NC}$message"
            ;;
        SUCCESS)
            echo -e "${GREEN}✓${NC} $message"
            ;;
        WARNING)
            echo -e "${YELLOW}⚠${NC} $message"
            ;;
        ERROR)
            echo -e "${RED}✗${NC} $message"
            ;;
        STEP)
            echo -e "\n${BLUE}➜${NC} $message"
            ;;
    esac
}

log_verbose() {
    if [ "$VERBOSE" = true ]; then
        log INFO "$@"
    fi
}

die() {
    log ERROR "$1"
    exit "${2:-1}"
}

run_command() {
    local description="$1"
    shift
    local command="$@"

    TASKS_TOTAL=$((TASKS_TOTAL + 1))

    log_verbose "Running: $command"

    if [ "$DRY_RUN" = true ]; then
        log WARNING "[DRY-RUN] Would run: $command"
        TASKS_SKIPPED=$((TASKS_SKIPPED + 1))
        return 0
    fi

    if eval "$command" >> "$LOG_FILE" 2>&1; then
        log SUCCESS "$description"
        TASKS_COMPLETED=$((TASKS_COMPLETED + 1))
        return 0
    else
        log ERROR "$description (failed)"
        TASKS_FAILED=$((TASKS_FAILED + 1))
        return 1
    fi
}

check_prerequisites() {
    log STEP "Checking prerequisites..."

    # Check if we're in the project directory
    if [ ! -f "$PROJECT_DIR/artisan" ]; then
        die "Not in Laravel project directory. artisan file not found." 2
    fi

    # Check if storage directory exists
    if [ ! -d "$PROJECT_DIR/storage" ]; then
        die "Storage directory not found. Is this a Laravel project?" 2
    fi

    # Auto-detect Docker usage
    if [ -z "$USE_DOCKER" ]; then
        if [ -f "$PROJECT_DIR/docker-compose.yml" ] && docker-compose ps 2>/dev/null | grep -q "book_library_app"; then
            USE_DOCKER=true
            log_verbose "Docker environment detected"
        else
            USE_DOCKER=false
            log_verbose "No Docker environment detected, using local PHP"
        fi
    fi

    # Check PHP availability
    if [ "$USE_DOCKER" = false ]; then
        if ! command -v php &> /dev/null; then
            die "PHP not found. Install PHP or use --docker flag." 2
        fi
        log_verbose "PHP version: $(php -v | head -n 1)"
    else
        if ! command -v docker-compose &> /dev/null; then
            die "docker-compose not found. Install docker-compose or don't use --docker flag." 2
        fi
    fi

    log SUCCESS "Prerequisites check passed"
}

run_migrations() {
    log STEP "Running database migrations..."

    local artisan_cmd="php artisan migrate --force"

    if [ "$USE_DOCKER" = true ]; then
        artisan_cmd="docker-compose exec -T app $artisan_cmd"
    fi

    if run_command "Database migrations" "$artisan_cmd"; then
        return 0
    else
        die "Migration failed. Check $LOG_FILE for details." 3
    fi
}

create_storage_directories() {
    log STEP "Creating storage directories..."

    local directories=(
        "storage/csv-imports"
        "storage/csv-exports"
        "storage/csv-templates"
        "storage/logs/csv-imports"
    )

    for dir in "${directories[@]}"; do
        local full_path="$PROJECT_DIR/$dir"

        if [ -d "$full_path" ]; then
            log_verbose "Directory already exists: $dir"
            TASKS_SKIPPED=$((TASKS_SKIPPED + 1))
        else
            if run_command "Create directory: $dir" "mkdir -p '$full_path'"; then
                # Create .gitkeep file
                if [ "$DRY_RUN" = false ]; then
                    touch "$full_path/.gitkeep"
                fi
            else
                die "Failed to create directory: $dir" 4
            fi
        fi
    done

    log SUCCESS "All storage directories verified"
}

set_permissions() {
    if [ "$SKIP_PERMS" = true ]; then
        log WARNING "Skipping permission changes (--skip-perms flag)"
        return 0
    fi

    log STEP "Setting directory permissions..."

    local directories=(
        "storage/csv-imports"
        "storage/csv-exports"
        "storage/csv-templates"
        "storage/logs/csv-imports"
    )

    for dir in "${directories[@]}"; do
        local full_path="$PROJECT_DIR/$dir"

        if run_command "Set permissions (775): $dir" "chmod -R 775 '$full_path'"; then
            log_verbose "Permissions set for: $dir"
        else
            log WARNING "Failed to set permissions for: $dir (continuing...)"
        fi
    done

    log SUCCESS "Directory permissions configured"
}

verify_templates() {
    log STEP "Verifying CSV templates..."

    local template_dir="$PROJECT_DIR/storage/csv-templates"
    local required_files=(
        "README.md"
        "book-import-template.csv"
        "book-import-example.csv"
    )

    local missing_files=()

    for file in "${required_files[@]}"; do
        if [ -f "$template_dir/$file" ]; then
            log_verbose "Template found: $file"
            TASKS_COMPLETED=$((TASKS_COMPLETED + 1))
        else
            log WARNING "Template missing: $file"
            missing_files+=("$file")
            TASKS_FAILED=$((TASKS_FAILED + 1))
        fi
        TASKS_TOTAL=$((TASKS_TOTAL + 1))
    done

    if [ ${#missing_files[@]} -eq 0 ]; then
        log SUCCESS "All required CSV templates verified"
        return 0
    else
        log WARNING "Missing templates: ${missing_files[*]}"
        log WARNING "You may need to regenerate templates or restore from backup"
        return 1
    fi
}

print_summary() {
    echo ""
    echo "============================================"
    echo "  CSV System Deployment Summary"
    echo "============================================"
    echo ""
    echo -e "Total tasks:      ${BLUE}$TASKS_TOTAL${NC}"
    echo -e "Completed:        ${GREEN}$TASKS_COMPLETED${NC}"
    echo -e "Skipped:          ${YELLOW}$TASKS_SKIPPED${NC}"
    echo -e "Failed:           ${RED}$TASKS_FAILED${NC}"
    echo ""
    echo -e "Environment:      $([ "$USE_DOCKER" = true ] && echo "Docker" || echo "Local PHP")"
    echo -e "Log file:         $LOG_FILE"
    echo ""

    if [ "$DRY_RUN" = true ]; then
        echo -e "${YELLOW}NOTE: This was a dry-run. No changes were made.${NC}"
        echo -e "${YELLOW}Run without --dry-run to apply changes.${NC}"
        echo ""
    fi

    if [ $TASKS_FAILED -eq 0 ] && [ "$DRY_RUN" = false ]; then
        echo -e "${GREEN}✓ Deployment completed successfully!${NC}"
        echo ""
        echo "Next steps:"
        echo "  1. Review the log file for details"
        echo "  2. Test CSV import/export in admin panel"
        echo "  3. Check /admin/csv-import to verify system is ready"
        echo ""
        return 0
    elif [ $TASKS_FAILED -gt 0 ]; then
        echo -e "${RED}✗ Deployment completed with errors${NC}"
        echo -e "${RED}  Please review the log file: $LOG_FILE${NC}"
        echo ""
        return 1
    fi
}

show_help() {
    cat << EOF
CSV Import/Export System Deployment Script

USAGE:
    $0 [OPTIONS]

OPTIONS:
    --dry-run       Show what would be done without making changes
    --verbose       Show detailed output during execution
    --skip-perms    Skip permission changes (useful for restrictive environments)
    --docker        Force using Docker environment
    --no-docker     Force using local PHP (no Docker)
    --help          Show this help message

EXAMPLES:
    # Run deployment (auto-detect environment)
    $0

    # Dry-run to see what would be done
    $0 --dry-run --verbose

    # Run in Docker environment with verbose output
    $0 --docker --verbose

    # Skip permission changes (for testing)
    $0 --skip-perms

DESCRIPTION:
    This script automates the deployment checklist tasks for the CSV
    import/export system:

    1. Check prerequisites (Laravel project, PHP/Docker availability)
    2. Run database migrations
    3. Create storage directories (csv-imports, csv-exports, etc.)
    4. Set proper permissions (775) on CSV directories
    5. Verify CSV templates exist

    All operations are logged to: storage/logs/csv-deploy-TIMESTAMP.log

EXIT CODES:
    0 - Success
    1 - General error
    2 - Prerequisites not met
    3 - Migration failed
    4 - Directory creation failed
    5 - Permission change failed

For more information, see: CSV_IMPORT_TODO.md section 12.3

EOF
}

################################################################################
# Main Script
################################################################################

main() {
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --dry-run)
                DRY_RUN=true
                shift
                ;;
            --verbose)
                VERBOSE=true
                shift
                ;;
            --skip-perms)
                SKIP_PERMS=true
                shift
                ;;
            --docker)
                USE_DOCKER=true
                shift
                ;;
            --no-docker)
                USE_DOCKER=false
                shift
                ;;
            --help|-h)
                show_help
                exit 0
                ;;
            *)
                echo "Unknown option: $1"
                echo "Use --help for usage information"
                exit 1
                ;;
        esac
    done

    # Print header
    echo ""
    echo "============================================"
    echo "  CSV System Deployment Script"
    echo "============================================"
    echo ""

    if [ "$DRY_RUN" = true ]; then
        echo -e "${YELLOW}Running in DRY-RUN mode - no changes will be made${NC}"
        echo ""
    fi

    # Create log directory if it doesn't exist
    mkdir -p "$PROJECT_DIR/storage/logs"

    # Initialize log file
    echo "CSV System Deployment Log - $(date)" > "$LOG_FILE"
    echo "Project: $PROJECT_DIR" >> "$LOG_FILE"
    echo "Dry-run: $DRY_RUN" >> "$LOG_FILE"
    echo "========================================" >> "$LOG_FILE"
    echo "" >> "$LOG_FILE"

    # Run deployment steps
    check_prerequisites
    run_migrations
    create_storage_directories
    set_permissions
    verify_templates

    # Print summary
    print_summary

    # Exit with appropriate code
    if [ $TASKS_FAILED -gt 0 ]; then
        exit 1
    else
        exit 0
    fi
}

# Run main function
main "$@"
