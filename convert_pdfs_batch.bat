@echo off
REM Batch PDF Converter for Windows - Remove Object Streams (PDF 1.5+)
REM
REM Requirements:
REM   - QPDF for Windows: http://qpdf.sourceforge.net/
REM   - Add qpdf to PATH or place qpdf.exe in same directory
REM
REM Usage:
REM   1. Place this script in folder with your PDF files
REM   2. Double-click to run
REM   3. Converted files will be in .\converted\ subdirectory

setlocal enabledelayedexpansion

REM Configuration
set "OUTPUT_DIR=converted"
set "LOG_FILE=conversion_log.txt"

REM Create output directory
if not exist "%OUTPUT_DIR%" mkdir "%OUTPUT_DIR%"

REM Initialize log
echo PDF Conversion Started: %date% %time% > "%LOG_FILE%"
echo Method: QPDF >> "%LOG_FILE%"
echo ---------------------------------------- >> "%LOG_FILE%"

REM Check if QPDF is available
where qpdf >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: qpdf not found!
    echo Please download from: http://qpdf.sourceforge.net/
    echo Add qpdf.exe to PATH or place in same directory as this script
    pause
    exit /b 1
)

echo Using QPDF method (preserves compression, minimal size increase)
echo.

REM Statistics
set /a TOTAL=0
set /a SUCCESS=0
set /a FAILED=0
set /a SKIPPED=0

echo Scanning for PDF files...
echo.

REM Process all PDFs
for %%F in (*.pdf) do (
    set /a TOTAL+=1
    set "FILENAME=%%~nxF"
    set "OUTPUT_FILE=%OUTPUT_DIR%\%%~nxF"

    REM Skip if already converted
    if exist "!OUTPUT_FILE!" (
        echo [SKIP] !FILENAME! - already exists
        set /a SKIPPED+=1
    ) else (
        echo Processing: !FILENAME!

        REM Get original size
        for %%A in ("%%F") do set SIZE_BEFORE=%%~zA

        REM Convert with QPDF
        qpdf --object-streams=disable "%%F" "!OUTPUT_FILE!" >> "%LOG_FILE%" 2>&1

        if !errorlevel! equ 0 (
            set /a SUCCESS+=1

            REM Get new size
            for %%A in ("!OUTPUT_FILE!") do set SIZE_AFTER=%%~zA

            REM Calculate percentage (approximate)
            set /a INCREASE=!SIZE_AFTER! - !SIZE_BEFORE!
            set /a PERCENT=!INCREASE! * 100 / !SIZE_BEFORE!

            echo   [SUCCESS] Original: !SIZE_BEFORE! bytes, New: !SIZE_AFTER! bytes, Change: +!PERCENT!%%
            echo [SUCCESS] !FILENAME! ^| Size: !SIZE_BEFORE! -^> !SIZE_AFTER! ^(+!PERCENT!%%^) >> "%LOG_FILE%"
        ) else (
            set /a FAILED+=1
            echo   [FAILED] Check log for details
            echo [FAILED] !FILENAME! ^| Error occurred >> "%LOG_FILE%"
        )
        echo.
    )
)

REM Summary
echo ========================================
echo          CONVERSION SUMMARY
echo ========================================
echo Total PDFs found:       %TOTAL%
echo Successfully converted: %SUCCESS%
echo Failed:                 %FAILED%
echo Skipped (exist):        %SKIPPED%
echo.
echo Converted files saved to: %OUTPUT_DIR%\
echo Detailed log saved to: %LOG_FILE%
echo ========================================
echo.

REM Add summary to log
echo. >> "%LOG_FILE%"
echo ======================================== >> "%LOG_FILE%"
echo SUMMARY >> "%LOG_FILE%"
echo ======================================== >> "%LOG_FILE%"
echo Total: %TOTAL% ^| Success: %SUCCESS% ^| Failed: %FAILED% ^| Skipped: %SKIPPED% >> "%LOG_FILE%"
echo Completed: %date% %time% >> "%LOG_FILE%"

pause
