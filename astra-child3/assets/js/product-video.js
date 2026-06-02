/**
 * Product Video JavaScript
 * جاوااسکریپت ویدیو محصول - مودال استوری
 * سازگار با قالب Astra
 */

;(($) => {
  // Video Story Modal Class
  class VideoStoryModal {
    constructor() {
      this.modal = null
      this.video = null
      this.iframe = null
      this.progressBar = null
      this.isMuted = false
      this.isInitialized = false
      this.init()
    }

    init() {
      this.createModal()
      this.setupVideoThumbnail()
      this.bindEvents()
      this.isInitialized = true
    }

    createModal() {
      const modalHTML = `
        <div class="video-story-modal" id="videoStoryModal">
          <div class="video-story-backdrop"></div>
          <div class="video-story-container">
            <div class="video-story-progress">
              <div class="video-story-progress-bar"></div>
            </div>
            <div class="video-story-header">
              <div class="video-story-info">
                <img class="video-story-avatar" src="/placeholder.svg" alt="">
                <div>
                  <p class="video-story-title"></p>
                  <p class="video-story-subtitle">ویدیو محصول</p>
                </div>
              </div>
              <button class="video-story-close" aria-label="بستن">
                <span class="close-text">بستن</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>
            <div class="video-story-content">
              <div class="video-story-loading"></div>
            </div>
            <button class="video-story-mute" aria-label="قطع/وصل صدا">
              <svg class="unmuted" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
              </svg>
              <svg class="muted" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="display:none;">
                <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
              </svg>
            </button>
            <div class="video-story-cta">
              <a href="#">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                مشاهده محصول
              </a>
            </div>
          </div>
        </div>
      `

      $("body").append(modalHTML)
      this.modal = $("#videoStoryModal")
      this.progressBar = this.modal.find(".video-story-progress-bar")
    }

    setupVideoThumbnail() {
      if (this.isInitialized) return

      const $videoItem = $(".product-video-gallery-item")
      if (!$videoItem.length) return

      const $thumbsList = $(".khoshtip-gallery-thumbnails")
      if (!$thumbsList.length) return

      const videoData = {
        url: $videoItem.data("video-url"),
        type: $videoItem.data("video-type"),
        title: $videoItem.data("product-title"),
        productUrl: $videoItem.data("product-url"),
        image: $videoItem.data("product-image") || $videoItem.find("img").attr("src"),
      }

      // Check if video thumbnail already added
      if ($thumbsList.find(".product-video-thumb").length > 0) {
        return
      }

      // Create video thumbnail
      const $videoThumb = $(`
        <div class="khoshtip-thumbnail product-video-thumb">
          <img src="${videoData.image}" alt="ویدیو محصول" data-image="${videoData.image}">
          <span class="video-thumb-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M8 5v14l11-7z"/>
            </svg>
          </span>
        </div>
      `)

      // Store data in element
      $videoThumb.data("video", videoData)
      $thumbsList.append($videoThumb)
    }

    bindEvents() {
      const self = this

      // Click on video thumbnail
      $(document).on("click touchend", ".product-video-thumb", function (e) {
        e.preventDefault()
        e.stopPropagation()
        e.stopImmediatePropagation()

        const data = $(this).data("video")
        if (data) {
          self.open(data.url, data.type, data.title, data.productUrl, data.image)
        }
        return false
      })

      // Video gallery item click
      const videoSelectors = [
        ".product-video-gallery-item",
        ".product-video-gallery-item a",
        ".product-video-gallery-item .product-video-link",
        ".product-video-gallery-item img",
        ".product-video-gallery-item .video-play-overlay",
        ".video-thumb-trigger",
        ".video-thumb-trigger a",
        ".video-thumb-trigger img",
      ].join(", ")

      $(document).on("click touchend", videoSelectors, (e) => {
        const $target = $(e.target)
        const $item = $target.closest(".product-video-gallery-item, .video-thumb-trigger")

        if ($item.length && $item.data("video-url")) {
          e.preventDefault()
          e.stopPropagation()
          e.stopImmediatePropagation()

          const videoUrl = $item.data("video-url")
          const videoType = $item.data("video-type") || "direct"
          const productTitle = $item.data("product-title") || "محصول"
          const productUrl = $item.data("product-url") || "#"
          const productImage = $item.data("product-image") || ""

          if (videoUrl) {
            self.open(videoUrl, videoType, productTitle, productUrl, productImage)
          }
          return false
        }
      })

      // Capture phase for events
      document.addEventListener(
        "click",
        (e) => {
          const target = e.target
          const videoItem = target.closest(".product-video-gallery-item, .video-thumb-trigger")

          if (videoItem && videoItem.dataset.videoUrl) {
            e.preventDefault()
            e.stopPropagation()
            e.stopImmediatePropagation()

            const videoUrl = videoItem.dataset.videoUrl
            const videoType = videoItem.dataset.videoType || "direct"
            const productTitle = videoItem.dataset.productTitle || "محصول"
            const productUrl = videoItem.dataset.productUrl || "#"
            const productImage = videoItem.dataset.productImage || ""

            if (videoUrl) {
              self.open(videoUrl, videoType, productTitle, productUrl, productImage)
            }
            return false
          }
        },
        true,
      )

      document.addEventListener(
        "touchend",
        (e) => {
          const target = e.target
          const videoItem = target.closest(".product-video-gallery-item, .video-thumb-trigger")

          if (videoItem && videoItem.dataset.videoUrl) {
            e.preventDefault()
            e.stopPropagation()
            e.stopImmediatePropagation()

            const videoUrl = videoItem.dataset.videoUrl
            const videoType = videoItem.dataset.videoType || "direct"
            const productTitle = videoItem.dataset.productTitle || "محصول"
            const productUrl = videoItem.dataset.productUrl || "#"
            const productImage = videoItem.dataset.productImage || ""

            if (videoUrl) {
              self.open(videoUrl, videoType, productTitle, productUrl, productImage)
            }
            return false
          }
        },
        true,
      )

      // Close modal
      $(document).on("click touchend", ".video-story-close", (e) => {
        e.preventDefault()
        e.stopPropagation()
        self.close()
      })

      $(document).on("click touchend", ".video-story-backdrop", (e) => {
        e.preventDefault()
        e.stopPropagation()
        self.close()
      })

      // Mute toggle
      $(document).on("click touchend", ".video-story-mute", (e) => {
        e.preventDefault()
        e.stopPropagation()
        self.toggleMute()
      })

      // ESC key to close
      $(document).on("keydown", (e) => {
        if (e.key === "Escape" && self.modal.hasClass("active")) {
          self.close()
        }
      })
    }

    open(videoUrl, videoType, productTitle, productUrl, productImage) {
      const $content = this.modal.find(".video-story-content")
      const $title = this.modal.find(".video-story-title")
      const $avatar = this.modal.find(".video-story-avatar")
      const $cta = this.modal.find(".video-story-cta a")

      // Set product info
      $title.text(productTitle)
      $avatar.attr("src", productImage || "/wp-content/themes/astra-child3/assets/images/default-avatar.png")
      $cta.attr("href", productUrl)

      // Clear previous content
      $content.html('<div class="video-story-loading"></div>')

      // Add video based on type
      if (videoType === "youtube" || videoType === "vimeo") {
        const iframe = $("<iframe>", {
          src: videoUrl,
          frameborder: "0",
          allow: "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture",
          allowfullscreen: true,
        })
        $content.html(iframe)
        this.iframe = iframe[0]
        this.video = null

        // Hide mute button for embeds
        this.modal.find(".video-story-mute").hide()
      } else {
        const video = $("<video>", {
          src: videoUrl,
          autoplay: true,
          loop: true,
          playsinline: true,
          muted: this.isMuted,
        })

        video.on("loadedmetadata", () => {
          this.modal.find(".video-story-loading").remove()
        })

        video.on("timeupdate", () => {
          if (this.video) {
            const progress = (this.video.currentTime / this.video.duration) * 100
            this.progressBar.css("width", progress + "%")
          }
        })

        $content.html(video)
        this.video = video[0]
        this.iframe = null

        // Show mute button for direct videos
        this.modal.find(".video-story-mute").show()
        this.updateMuteIcon()
      }

      // Show modal
      this.modal.addClass("active")
      $("body").css("overflow", "hidden")
    }

    close() {
      // Stop video
      if (this.video) {
        this.video.pause()
        this.video.currentTime = 0
      }

      // Clear iframe
      if (this.iframe) {
        this.iframe.src = ""
      }

      // Reset progress
      this.progressBar.css("width", "0")

      // Hide modal
      this.modal.removeClass("active")
      $("body").css("overflow", "")
    }

    toggleMute() {
      if (this.video) {
        this.isMuted = !this.isMuted
        this.video.muted = this.isMuted
        this.updateMuteIcon()
      }
    }

    updateMuteIcon() {
      const $muteBtn = this.modal.find(".video-story-mute")
      if (this.isMuted) {
        $muteBtn.find(".unmuted").hide()
        $muteBtn.find(".muted").show()
      } else {
        $muteBtn.find(".unmuted").show()
        $muteBtn.find(".muted").hide()
      }
    }
  }

  // Initialize on DOM ready
  $(document).ready(() => {
    new VideoStoryModal()
  })
})(window.jQuery)
