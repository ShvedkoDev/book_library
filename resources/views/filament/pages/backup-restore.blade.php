<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Create Full Backup</x-slot>
            <div class="filament-forms-component-wrapper">
                <x-filament::button wire:click="createBackup" icon="heroicon-o-archive-box" size="lg">
                    Create Full Backup
                </x-filament::button>
                <x-filament::button color="gray" size="lg" wire:click="$refresh" class="ml-2" icon="heroicon-o-arrow-path">
                    Refresh
                </x-filament::button>
            </div>
            <div class="prose dark:prose-invert mt-4">
                <p>This will:</p>
                <ul class="list-disc pl-5">
                    <li>Dump the MySQL database (fallback to pure PHP if mysqldump is unavailable).</li>
                    <li>Archive storage files: <code>storage/app/public/</code> and <code>storage/app/uploads/</code>.</li>
                    <li>Create a downloadable archive (.zip if available, otherwise .tar) in <code>storage/app/full-backups/</code>.</li>
                </ul>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Create Files-Only Backup</x-slot>
            <div class="filament-forms-component-wrapper">
                <x-filament::button wire:click="createFilesBackup" icon="heroicon-o-folder" size="lg" color="info">
                    Create Files Backup
                </x-filament::button>
            </div>
            <div class="prose dark:prose-invert mt-4">
                <p>This will:</p>
                <ul class="list-disc pl-5">
                    <li><strong>Skip</strong> the database dump (faster, smaller).</li>
                    <li>Archive only storage files: <code>storage/app/public/</code> and <code>storage/app/uploads/</code>.</li>
                    <li>Create a downloadable archive (.zip if available, otherwise .tar) in <code>storage/app/full-backups/</code>.</li>
                </ul>
                <p class="text-sm text-gray-600 dark:text-gray-400">Use this for quick file backups without database overhead.</p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Create Split Backup (For Large Storage)</x-slot>
            <div class="filament-forms-component-wrapper">
                <x-filament::button wire:click="createSplitBackup" icon="heroicon-o-squares-2x2" size="lg" color="warning">
                    Create Split Backup
                </x-filament::button>
            </div>
            <div class="prose dark:prose-invert mt-4">
                <p>This will create multiple archive files (max <strong>2GB per part</strong>):</p>
                <ul class="list-disc pl-5">
                    <li><code>part1_database.zip</code> - Database dump</li>
                    <li><code>part2_config.zip</code> - Configuration files</li>
                    <li><code>part3_storage_public.zip</code> (or multiple parts if &gt;2GB)</li>
                    <li><code>partN_storage_uploads.zip</code> (or multiple parts if &gt;2GB)</li>
                    <li><code>MANIFEST.json</code> - Backup metadata and part list</li>
                </ul>
                <p class="text-sm text-yellow-600 dark:text-yellow-400"><strong>Best for large storage (10GB+)</strong> - Each part can be downloaded/uploaded separately.</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Files are stored in <code>storage/app/full-backups/split_backup_[timestamp]/</code></p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Restore Backup</x-slot>
            <div class="filament-forms-component-wrapper">
                <form wire:submit="restore">
                    {{ $this->form }}
                    <div class="mt-4">
                        <x-filament::button type="submit" icon="heroicon-o-arrow-path" size="lg">
                            Restore from ZIP
                        </x-filament::button>
                    </div>
                </form>
            </div>
            <div class="prose dark:prose-invert mt-4">
                <p>Upload a previously downloaded ZIP and restore files & database to the current environment.</p>
                <p><strong>Note:</strong> Restoration uses the current <code>.env</code> database connection.</p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
