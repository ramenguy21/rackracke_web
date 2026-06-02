import Sort from '@alpinejs/sort'

// Livewire 3 bundles and starts Alpine — do NOT call Alpine.start() here.
document.addEventListener('alpine:init', () => {
    Alpine.plugin(Sort)

    // ── Photo upload store ────────────────────────────────────────────
    Alpine.data('photoUpload', (initialPhotos = [], uploadUrl = '') => ({
        photos: initialPhotos,
        dragging: false,
        uploadUrl,

        get hasPhotos() {
            return this.photos.length > 0
        },

        onDrop(e) {
            this.dragging = false
            this.addFiles([...(e.dataTransfer?.files ?? [])])
        },

        onPick(e) {
            this.addFiles([...(e.target?.files ?? [])])
            e.target.value = ''
        },

        async addFiles(files) {
            const imageFiles = files.filter(f => f.type.startsWith('image/'))
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? ''

            for (const file of imageFiles) {
                const id = crypto.randomUUID()
                const blobUrl = URL.createObjectURL(file)
                this.photos.push({ id, url: blobUrl, serverPath: null, uploading: true, error: null })

                try {
                    const form = new FormData()
                    form.append('photo', file)
                    form.append('_token', csrf)
                    const res = await fetch(this.uploadUrl, { method: 'POST', body: form })
                    if (!res.ok) throw new Error(`Upload failed: ${res.status} ${res.statusText}`)
                    const data = await res.json()

                    URL.revokeObjectURL(blobUrl)
                    const idx = this.photos.findIndex(p => p.id === id)
                    if (idx !== -1) {
                        this.photos[idx].url = data.url
                        this.photos[idx].serverPath = data.path
                        this.photos[idx].uploading = false
                    }
                } catch (err) {
                    console.error('[rackrake] photo upload error:', err)
                    const idx = this.photos.findIndex(p => p.id === id)
                    if (idx !== -1) {
                        this.photos[idx].uploading = false
                        this.photos[idx].error = true
                    }
                }
                // NOTE: do NOT call syncPhotos() here — calling it per-upload causes
                // concurrent Livewire requests that race each other and overwrite the
                // photos array with a partial list. Sync once after all uploads finish.
            }

            // Single sync after all files are processed — no race condition
            this.syncPhotos()
        },

        remove(id) {
            const photo = this.photos.find(p => p.id === id)
            if (photo?.url?.startsWith('blob:')) URL.revokeObjectURL(photo.url)
            this.photos = this.photos.filter(p => p.id !== id)
            this.syncPhotos()
        },

        onSortEnd() {
            this.syncPhotos()
        },

        syncPhotos() {
            const paths = this.photos
                .filter(p => p.serverPath)
                .map(p => p.serverPath)
            this.$wire.call('setPhotos', paths)
        },
    }))
})
