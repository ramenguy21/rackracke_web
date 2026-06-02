import Sort from '@alpinejs/sort'

// Livewire 3 bundles and starts Alpine — do NOT call Alpine.start() here.
// Register plugins and components via alpine:init so they're available
// on the single Livewire-managed Alpine instance.
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
                const entry = { id, url: blobUrl, serverPath: null, uploading: true, error: null }
                this.photos.push(entry)

                try {
                    const form = new FormData()
                    form.append('photo', file)
                    form.append('_token', csrf)
                    const res = await fetch(this.uploadUrl, { method: 'POST', body: form })
                    if (!res.ok) throw new Error(res.statusText)
                    const data = await res.json()

                    URL.revokeObjectURL(blobUrl)
                    entry.url = data.url
                    entry.serverPath = data.path
                    entry.uploading = false
                } catch {
                    entry.uploading = false
                    entry.error = true
                }

                this.syncPhotos()
            }
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
