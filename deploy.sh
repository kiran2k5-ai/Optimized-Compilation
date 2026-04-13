#!/bin/bash
# ============================================
# CodeRunner + Pyodide Integration Deploy Script
# Automates file copying and initial setup
# ============================================

set -e

color_red='\033[0;31m'
color_green='\033[0;32m'
color_yellow='\033[1;33m'
color_blue='\033[0;34m'
nc='\033[0m' # No Color

echo -e "${color_blue}========================================${nc}"
echo -e "${color_blue}CodeRunner + Pyodide Integration Deploy${nc}"
echo -e "${color_blue}========================================${nc}\n"

# Configuration
MOODLE_PATH="/var/www/moodle"  # Change this to your Moodle path
CODERUNNER_PATH="$MOODLE_PATH/question/type/coderunner"

# Color functions
ok() {
    echo -e "${color_green}✓${nc} $1"
}

error() {
    echo -e "${color_red}✗${nc} $1"
}

warn() {
    echo -e "${color_yellow}⚠${nc} $1"
}

info() {
    echo -e "${color_blue}ℹ${nc} $1"
}

# Check if paths exist
if [ ! -d "$MOODLE_PATH" ]; then
    error "Moodle path not found: $MOODLE_PATH"
    error "Please edit this script and set MOODLE_PATH correctly"
    exit 1
fi

if [ ! -d "$CODERUNNER_PATH" ]; then
    error "CodeRunner path not found: $CODERUNNER_PATH"
    error "Please install CodeRunner plugin first"
    exit 1
fi

ok "Moodle path verified: $MOODLE_PATH"
ok "CodeRunner path verified: $CODERUNNER_PATH"

# Check permissions
if [ ! -w "$CODERUNNER_PATH" ]; then
    error "No write permission to CodeRunner directory"
    error "Run with: sudo bash $0"
    exit 1
fi

ok "Write permissions verified"

# Create required directories
info "Creating required directories..."
mkdir -p "$CODERUNNER_PATH/tests"
mkdir -p "$CODERUNNER_PATH/examples"
ok "Directories created/verified"

# Check if core files exist
info "Verifying core files..."
CORE_FILES=(
    "enable_pyodide.php"
    "jobe_api_mock.php"
    "pyodide_executor.js"
    "setup_pyodide.php"
    "renderer.php"
)

MISSING_FILES=0
for file in "${CORE_FILES[@]}"; do
    if [ -f "$CODERUNNER_PATH/$file" ]; then
        ok "Found: $file"
    else
        error "Missing: $file"
        MISSING_FILES=$((MISSING_FILES + 1))
    fi
done

if [ $MISSING_FILES -gt 0 ]; then
    error "Some core files are missing. Please copy them first."
    exit 1
fi

# Check for new files that might need copying
info "Checking for new integration files..."

NEW_FILES=(
    "lib_integration.php"
    "tests/integration_test.php"
    "examples/sample_questions.sql"
)

for file in "${NEW_FILES[@]}"; do
    if [ -f "$CODERUNNER_PATH/$file" ]; then
        ok "Found: $file"
    else
        warn "New file not found: $file (may need manual copy)"
    fi
done

# Set proper permissions
info "Setting file permissions..."
find "$CODERUNNER_PATH" -type f -name "*.php" -exec chmod 644 {} \;
find "$CODERUNNER_PATH" -type f -name "*.js" -exec chmod 644 {} \;
find "$CODERUNNER_PATH" -type d -exec chmod 755 {} \;
ok "Permissions set"

# Check Moodle config
info "Checking Moodle configuration..."

# Try to read config
if grep -q "use_local_pyodide" "$MOODLE_PATH/config-dist.php" 2>/dev/null; then
    ok "Pyodide config setting found"
else
    warn "Pyodide config not yet set (will be set on first admin visit)"
fi

# Test PHP files for syntax
info "Validating PHP files..."
for file in "$CODERUNNER_PATH"/*.php; do
    if [ -f "$file" ]; then
        if php -l "$file" > /dev/null 2>&1; then
            ok "PHP valid: $(basename "$file")"
        else
            error "PHP syntax error in: $(basename "$file")"
            php -l "$file"
            exit 1
        fi
    fi
done

# Download Pyodide documentation (optional)
info "Checking for Pyodide documentation..."
if [ ! -f "$CODERUNNER_PATH/PYODIDE_INTEGRATION.md" ]; then
    warn "Documentation files not found in CodeRunner directory"
else
    ok "Documentation files present"
fi

# Summary
echo ""
echo -e "${color_blue}========================================${nc}"
echo -e "${color_green}Deployment Summary${nc}"
echo -e "${color_blue}========================================${nc}"
echo ""
ok "CodeRunner path verified"
ok "Directories created/checked"
ok "Core files verified"
ok "PHP files validate"
ok "Permissions set correctly"
echo ""

# Next steps
info "Next steps:"
echo "  1. Run Moodle cron:"
echo "     php $MOODLE_PATH/admin/cli/cron.php"
echo ""
echo "  2. Test integration:"
echo "     http://localhost/question/type/coderunner/tests/integration_test.php"
echo ""
echo "  3. Review documentation:"
echo "     $CODERUNNER_PATH/PYODIDE_INTEGRATION.md"
echo ""
echo "  4. Create test question:"
echo "     Moodle Admin → Question Bank → Create Question"
echo ""
echo "  5. Test student submission:"
echo "     Create quiz → Add CodeRunner question → Student attempt"
echo ""

echo -e "${color_green}✓ Deployment preparation complete!${nc}"
echo ""

exit 0
