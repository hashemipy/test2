/**
 * Video Hover Preview for Product Cards
 * نمایش 5 ثانیه ویدیو بر روی محصولات در صفحه اصلی
 * Shows 5 seconds of product video on hover/long press
 */
;(() => {
  let activeOverlay = null
  const hoverTimeout = null
  let videoTimeout = null
  const longPressTimeout = null

  // Initialize when DOM is ready
  document.addEventListener("DOMContentLoaded", initVideoPreview)

  // Re-initialize after AJAX content loads
  document.addEventListener("ajaxComplete", initVideoPreview)

  function initVideoPreview() {
    const playButtons = document.querySelectorAll(".product-video-play-btn:not([data-initialized])")

    playButtons.forEach((btn) => {
      btn.setAttribute("data-initialized", "true")

      const videoSrc = btn.dataset.videoSrc
      const videoType = btn.dataset.videoType
      const productCard = btn.closest(".product-card")

      if (!productCard || !videoSrc) return

      // Desktop: Click to play
      btn.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()
        console.log("[v0] Video clicked, playing preview", videoSrc)
        showVideoPreview(productCard, videoSrc, videoType)
      })

      // Mobile: Touch to play
      btn.addEventListener("touchend", (e) => {
        e.preventDefault()
        e.stopPropagation()
        console.log("[v0] Video touched, playing preview", videoSrc)
        showVideoPreview(productCard, videoSrc, videoType)
      })

      btn.addEventListener("mousedown", (e) => {
        e.preventDefault()
        e.stopPropagation()
      })

      btn.addEventListener("touchstart", (e) => {
        e.preventDefault()
        e.stopPropagation()
      })
    })
  }

  function showVideoPreview(container, videoSrc, videoType) {
    console.log("[v0] Showing video preview", { videoType, hasSrc: !!videoSrc })

    // Close any active overlay
    closeActiveOverlay()

    // Create overlay
    const overlay = document.createElement("div")
    overlay.className = "video-hover-overlay"

    // Add loading spinner
    const spinner = document.createElement("div")
    spinner.className = "video-loading-spinner"
    overlay.appendChild(spinner)

    container.style.position = "relative"
    container.appendChild(overlay)
    activeOverlay = overlay

    // Show overlay
    requestAnimationFrame(() => {
      overlay.classList.add("active")
    })

    // Determine video type and create element
    if (videoType === "media" || videoSrc.match(/\.(mp4|webm|ogg)$/i)) {
      // Direct video file
      const video = document.createElement("video")
      video.src = videoSrc
      video.autoplay = true
      video.muted = true
      video.playsInline = true
      video.loop = false

      video.addEventListener("loadeddata", () => {
        spinner.remove()
      })

      video.addEventListener("error", () => {
        console.log("[v0] Video error")
        closeActiveOverlay()
      })

      overlay.appendChild(video)

      // Auto close after 5 seconds
      videoTimeout = setTimeout(() => {
        console.log("[v0] Auto-closing video after 5 seconds")
        closeActiveOverlay()
      }, 5000)
    } else if (videoSrc.includes("youtube.com") || videoSrc.includes("youtu.be")) {
      // YouTube video
      const videoId = extractYouTubeId(videoSrc)
      if (videoId) {
        const iframe = document.createElement("iframe")
        iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&controls=0&start=0&end=5`
        iframe.allow = "autoplay"
        iframe.onload = () => spinner.remove()
        overlay.appendChild(iframe)

        videoTimeout = setTimeout(() => {
          closeActiveOverlay()
        }, 5500)
      }
    } else if (videoSrc.includes("aparat.com")) {
      // Aparat video
      const videoId = extractAparatId(videoSrc)
      if (videoId) {
        const iframe = document.createElement("iframe")
        iframe.src = `https://www.aparat.com/video/video/embed/videohash/${videoId}/vt/frame?autoplay=true&muted=true`
        iframe.allow = "autoplay"
        iframe.onload = () => spinner.remove()
        overlay.appendChild(iframe)

        videoTimeout = setTimeout(() => {
          closeActiveOverlay()
        }, 5500)
      }
    } else if (videoSrc.includes("vimeo.com")) {
      // Vimeo video
      const videoId = extractVimeoId(videoSrc)
      if (videoId) {
        const iframe = document.createElement("iframe")
        iframe.src = `https://player.vimeo.com/video/${videoId}?autoplay=1&muted=1`
        iframe.allow = "autoplay"
        iframe.onload = () => spinner.remove()
        overlay.appendChild(iframe)

        videoTimeout = setTimeout(() => {
          closeActiveOverlay()
        }, 5500)
      }
    }

    // Close on click outside or on overlay
    overlay.addEventListener("click", (e) => {
      closeActiveOverlay()
    })

    // Close on scroll
    window.addEventListener("scroll", closeActiveOverlay, { once: true })
  }

  function closeActiveOverlay() {
    if (activeOverlay) {
      activeOverlay.classList.remove("active")
      setTimeout(() => {
        if (activeOverlay && activeOverlay.parentNode) {
          activeOverlay.parentNode.removeChild(activeOverlay)
        }
        activeOverlay = null
      }, 300)
    }

    if (videoTimeout) {
      clearTimeout(videoTimeout)
      videoTimeout = null
    }
  }

  function extractYouTubeId(url) {
    const match = url.match(
      /(?:youtube\.com\/(?:[^/]+\/.+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([^"&?/\s]{11})/,
    )
    return match ? match[1] : null
  }

  function extractAparatId(url) {
    const match = url.match(/aparat\.com\/v\/([a-zA-Z0-9]+)/)
    return match ? match[1] : null
  }

  function extractVimeoId(url) {
    const match = url.match(/vimeo\.com\/(?:.*\/)?(\d+)/)
    return match ? match[1] : null
  }

  // Also watch for DOM changes (for infinite scroll)
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.addedNodes.length) {
        setTimeout(initVideoPreview, 100)
      }
    })
  })

  // Start observing product grids
  document.addEventListener("DOMContentLoaded", () => {
    const grids = document.querySelectorAll(".product-card, .products-grid, .product-grid")
    grids.forEach((grid) => {
      observer.observe(grid, { childList: true, subtree: true })
    })
  })
})()
