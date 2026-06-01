import Alpine from 'alpinejs'
import Sort from '@alpinejs/sort'

Alpine.plugin(Sort)

// ── Photo upload store ──────────────────────────────────────────────────────
// Used by the listing form. Handles client-side previews + ordering before
// Livewire takes over for the actual server upload.
Alpine.data('photoUpload', (initialPhotos = []) => ({
    photos: initialPhotos,   // [{ id, url, file?, uploading, error }]
    dragging: false,

    get hasPhotos() {
        return this.photos.length > 0
    },

    get coverPhoto() {
        return this.photos[0] ?? null
    },

    onDrop(e) {
        this.dragging = false
        const files = [...(e.dataTransfer?.files ?? [])]
        this.addFiles(files)
    },

    onPick(e) {
        const files = [...(e.target?.files ?? [])]
        this.addFiles(files)
        e.target.value = ''     // reset so same file can be re-picked
    },

    addFiles(files) {
        const images = files.filter(f => f.type.startsWith('image/'))
        for (const file of images) {
            const id   = crypto.randomUUID()
            const url  = URL.createObjectURL(file)
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

    // Called after sort ends (Alpine Sort fires 'sort' event on the container)
    onSortEnd() {
        this.$dispatch('photos-changed', { photos: this.photos })
    },
}))

// ── Page transition helper ──────────────────────────────────────────────────
document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('.page-enter').forEach(el => {
        el.classList.add('entered')
    })
})

Alpine.start()

window.Alpine = Alpine
