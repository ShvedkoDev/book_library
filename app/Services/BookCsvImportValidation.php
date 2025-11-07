<?php

namespace App\Services;

trait BookCsvImportValidation
{
    /**
     * Validate CSV headers
     */
    protected function validateHeaders(array $headers): void
    {
        // Get expected headers from field mapping
        $expectedHeaders = array_keys($this->fieldMapping);

        // Check for required columns
        $requiredColumns = ['ID', 'Title'];

        foreach ($requiredColumns as $required) {
            if (!in_array($required, $headers)) {
                $this->errors[] = "Missing required column: {$required}";
            }
        }

        // Warn about unexpected columns
        foreach ($headers as $header) {
            if (!in_array($header, $expectedHeaders) && !empty($header)) {
                $this->warnings[] = "Unexpected column found: {$header}";
            }
        }
    }

    /**
     * Validate a single data row
     */
    protected function validateRow(array $row, array $headers): void
    {
        $data = $this->mapRowToArray($row, $headers);

        // Validate required fields
        if (empty($data['title'])) {
            $this->errors[] = "Row {$this->currentRow}: Missing required field 'Title'";
        }

        // Validate string lengths
        $this->validateStringLengths($data);

        // Validate integer fields
        $this->validateIntegers($data);

        // Validate enum values
        $this->validateEnums($data);
    }

    /**
     * Validate string field lengths
     */
    protected function validateStringLengths(array $data): void
    {
        $maxLengths = $this->config['validation']['string_max_lengths'];

        foreach ($maxLengths as $field => $maxLength) {
            if (isset($data[$field]) && strlen($data[$field]) > $maxLength) {
                $this->warnings[] = "Row {$this->currentRow}: Field '{$field}' exceeds maximum length of {$maxLength} characters";
            }
        }
    }

    /**
     * Validate integer fields and ranges
     */
    protected function validateIntegers(array $data): void
    {
        $ranges = $this->config['validation']['integer_ranges'];

        foreach ($ranges as $field => $range) {
            if (isset($data[$field]) && !empty($data[$field])) {
                // Clean the value (remove question marks from years)
                $value = str_replace('?', '', $data[$field]);

                if (!is_numeric($value)) {
                    $this->warnings[] = "Row {$this->currentRow}: Field '{$field}' should be numeric, got '{$data[$field]}'";
                    continue;
                }

                $intValue = (int) $value;
                [$min, $max] = $range;

                if ($intValue < $min || $intValue > $max) {
                    $this->warnings[] = "Row {$this->currentRow}: Field '{$field}' value {$intValue} is out of range ({$min}-{$max})";
                }
            }
        }

        // Validate pages
        if (isset($data['pages']) && !empty($data['pages'])) {
            if (!is_numeric($data['pages']) || (int) $data['pages'] < 1) {
                $this->warnings[] = "Row {$this->currentRow}: Invalid pages value '{$data['pages']}'";
            }
        }
    }

    /**
     * Validate enum field values
     */
    protected function validateEnums(array $data): void
    {
        // Validate access level
        if (isset($data['access_level']) && !empty($data['access_level'])) {
            $accessLevelMapping = $this->config['access_level_mapping'];
            if (!isset($accessLevelMapping[$data['access_level']])) {
                $validValues = implode(', ', array_keys($accessLevelMapping));
                $this->warnings[] = "Row {$this->currentRow}: Invalid access level '{$data['access_level']}'. Valid values: {$validValues}";
            }
        }

        // Validate physical type
        if (isset($data['physical_type']) && !empty($data['physical_type'])) {
            $physicalTypeMapping = $this->config['physical_type_mapping'];
            if (!isset($physicalTypeMapping[$data['physical_type']])) {
                $validValues = implode(', ', array_unique(array_values($physicalTypeMapping)));
                $this->warnings[] = "Row {$this->currentRow}: Invalid physical type '{$data['physical_type']}'. Valid values: {$validValues}";
            }
        }
    }

    /**
     * Check if row is a header/mapping row
     */
    protected function isHeaderRow(?array $row): bool
    {
        if (!$row) {
            return false;
        }

        // Check if first cell contains database field mapping pattern
        return isset($row[0]) && (
            str_contains($row[0], 'books.') ||
            str_contains($row[0], 'classification_values.') ||
            str_contains($row[0], 'creators.') ||
            str_contains($row[0], 'languages.')
        );
    }

    /**
     * Check if row is empty
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (!empty(trim($cell))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Map CSV row to associative array
     */
    protected function mapRowToArray(array $row, array $headers): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            $value = $row[$index] ?? '';

            // Get mapped field name
            $fieldName = $this->fieldMapping[$header] ?? null;

            if ($fieldName) {
                $data[$fieldName] = trim($value);
            }
        }

        return $data;
    }

    /**
     * Get validation result
     */
    protected function getValidationResult(): array
    {
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'error_count' => count($this->errors),
            'warning_count' => count($this->warnings),
        ];
    }
}
