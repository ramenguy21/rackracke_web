import Sort from '@alpinejs/sort'

// Livewire 3 bundles and starts Alpine — do NOT call Alpine.start() here.
// Register plugins and components via alpine:init so they're available
// on the single Livewire-managed Alpine instance.
document.addEventListener('alpine:init', () => {
    Alpine.plugin(Sort)

    // ── Photo upload store ────────────────────────────────────────────
    Alpine.data('photoUpload', (initialPhotos = []) => ({
        photos: initialPhotos,
        dragging: false,

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

        addFiles(files) {
            for (const file of files.filter(f => f.type.startsWith('image/'))) {
                const id  = crypto.randomUUID()
                const url = URL.createObjectURL(file)
                this.photos.push({ id, url, file, uploading: false, error: null })
            }
            this.$dispatch('photos-changed', { photos: this.photos })
        },

        remove(id) {
            const photo = this.photos.find(p => p.id === id)
            if (photo?.url?.startsWith('blob:')) URL.revokeObjectURL(photo.url)
            this.photos = this.photos.filter(p => p.id !== id)
            this.$dispatch('photos-changed', { photos: this.photos })
        },

        onSortEnd() {
            this.$dispatch('photos-changed', { photos: this.photos })
        },
    }))
})
