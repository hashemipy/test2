// Main JavaScript for Khoshtip Kocholo Theme
;(() => {
  // Wait for DOM and jQuery to be ready
  if (typeof window.jQuery === "undefined") {
    console.error("jQuery is not loaded")
    return
  }

  const $ = window.jQuery
  const Swiper = window.Swiper

  // Declare the khoshtipSizeSearch variable

  $(document).ready(() => {
    console.log("[v0] khoshtipSizeSearch:", window.khoshtipSizeSearch)

    // Mobile Menu Toggle
    $("[data-mobile-menu-trigger]").on("click", () => {
      $("[data-mobile-menu]").css("right", "0")
    })

    $("[data-mobile-menu-close]").on("click", () => {
      $("[data-mobile-menu]").css("right", "-100%")
    })

    // Search Modal Toggle
    $("[data-search-trigger]").on("click", () => {
      $("[data-search-modal]").css("display", "flex")
    })

    $("[data-search-modal-close]").on("click", () => {
      $("[data-search-modal]").css("display", "none")
    })

    // Close modals on outside click
    $("[data-search-modal]").on("click", function (e) {
      if (e.target === this) {
        $(this).css("display", "none")
      }
    })

    console.log("[v0] Checking categories panel...")
    console.log("[v0] Panel trigger exists:", $(".k-categories-panel-trigger").length)
    console.log("[v0] Panel element exists:", $(".k-categories-panel").length)

    $(document).on("click", ".k-categories-panel-trigger, [data-panel-trigger]", (e) => {
      e.preventDefault()
      e.stopPropagation()
      console.log("[v0] Categories panel trigger clicked!")
      $(".k-categories-panel").css("display", "flex").hide().fadeIn(300)
      $("body").css("overflow", "hidden")

      if (window.innerWidth < 768) {
        $(".k-bottom-bar").addClass("k-bottom-bar-hidden")
        $(".k-bottom-bar-wave").addClass("k-bottom-bar-hidden")

        $(".k-categories-products").css("padding-bottom", "70px")
      }

      // Swap button icon and text to "بازگشت"
      const $trigger = $(".k-categories-panel-trigger")
      $trigger.data("original-html", $trigger.html())
      $trigger.html(
        '<span style="font-size: 24px;">🏠</span><span style="font-size: 11px; text-align: center; line-height: 1.2;">بازگشت</span>',
      )
    })

    $(".k-categories-panel-close").on("click", () => {
      console.log("[v0] Categories panel close clicked!")
      $(".k-categories-panel").fadeOut(300)
      $("body").css("overflow", "")

      if (window.innerWidth < 768) {
        $(".k-bottom-bar").removeClass("k-bottom-bar-hidden")
        $(".k-bottom-bar-wave").removeClass("k-bottom-bar-hidden")

        $(".k-categories-products").css("padding-bottom", "0")
      }

      // Restore original button
      const $trigger = $(".k-categories-panel-trigger")
      const originalHtml = $trigger.data("original-html")
      if (originalHtml) {
        $trigger.html(originalHtml)
      }
      
      // Set cookie to remember panel was closed (for auto-open feature)
      // Cookie expires after session ends (browser close)
      document.cookie = "k_panel_closed=1; path=/; SameSite=Lax"
    })

    let searchTimeout
    $(".k-panel-search-input").on("input", function () {
      const query = $(this).val().trim()

      clearTimeout(searchTimeout)

      if (query.length < 2) {
        $(".k-categories-products-inner").html(
          '<div style="padding: 20px; text-align: center; color: #999;"><p style="font-size: 16px; margin: 40px 0;">یک دسته‌بندی را انتخاب کنید یا جستجو کنید</p></div>',
        )
        return
      }

      searchTimeout = setTimeout(() => {
        // Show loading
        $(".k-categories-products-inner").html(
          '<div style="text-align: center; padding: 40px;"><div style="display: inline-block; width: 50px; height: 50px; border: 4px solid #f0f0f0; border-top-color: #ff6b9d; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px; color: #666;">در حال جستجو...</p></div>',
        )

        $.ajax({
          url: window.khoshtipSizeSearch?.ajaxurl || "/wp-admin/admin-ajax.php",
          type: "POST",
          data: {
            action: "khoshtip_search_products",
            nonce: window.khoshtipSizeSearch?.nonce,
            query: query,
          },
          success: (response) => {
            if (response.success && response.data.html) {
              $(".k-categories-products-inner").html(response.data.html)
            } else {
              $(".k-categories-products-inner").html(
                '<div style="text-align: center; padding: 40px; color: #999;"><p style="font-size: 16px;">هیچ محصولی یافت نشد</p></div>',
              )
            }
          },
          error: () => {
            $(".k-categories-products-inner").html(
              '<div style="text-align: center; padding: 40px; color: #e53935;"><p>خطا در جستجو</p></div>',
            )
          },
        })
      }, 500)
    })

    $(".k-panel-size-toggle").on("click", (e) => {
      e.stopPropagation()
      $(".k-panel-size-filter").slideToggle(200)
    })

    $(".k-panel-size-input").on("change", () => {
      const selectedSizes = []
      $(".k-panel-size-input:checked").each(function () {
        selectedSizes.push($(this).val())
      })

      if (selectedSizes.length === 0) {
        return
      }

      // Show loading
      $(".k-categories-products-inner").html(
        '<div style="text-align: center; padding: 40px;"><div style="display: inline-block; width: 50px; height: 50px; border: 4px solid #f0f0f0; border-top-color: #ff6b9d; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px; color: #666;">در حال جستجو بر اساس سایز...</p></div>',
      )

      // Search by size
      $.ajax({
        url: window.khoshtipSizeSearch?.ajaxurl || "/wp-admin/admin-ajax.php",
        type: "POST",
        data: {
          action: "khoshtip_search_by_size",
          nonce: window.khoshtipSizeSearch?.nonce,
          sizes: selectedSizes,
        },
        success: (response) => {
          if (response.success) {
            $(".k-categories-products-inner").html(response.data.html)
          } else {
            $(".k-categories-products-inner").html(
              '<div style="text-align: center; padding: 40px; color: #999;"><p>' +
                (response.data?.message || "هیچ محصولی یافت نشد") +
                "</p></div>",
            )
          }
        },
        error: () => {
          $(".k-categories-products-inner").html(
            '<div style="text-align: center; padding: 40px; color: #e53935;"><p>خطا در جستجو</p></div>',
          )
        },
      })
    })

    // Helper function to reinitialize swipers in categories panel
    function reinitializeCategoryPanelSwipers() {
      if (typeof Swiper === "undefined") return

      const $swipers = $(".k-categories-products-inner .swiper")
      
      $swipers.each(function() {
        const swiperEl = this
        
        // Destroy existing swiper if it exists
        if (swiperEl.swiper) {
          console.log("[v0] Destroying existing swiper")
          swiperEl.swiper.destroy(true, true)
        }
        
        console.log("[v0] Initializing new swiper")
        const horizontalSwiper = new Swiper(swiperEl, {
          slidesPerView: 2,
          spaceBetween: 16,
          loop: true,
          speed: 3000,
          autoplay: {
            delay: 0,
            disableOnInteraction: false,
          },
          freeMode: true,
          freeModeMomentum: false,
          breakpoints: {
            640: {
              slidesPerView: 2.5,
              spaceBetween: 20,
            },
            768: {
              slidesPerView: 3,
              spaceBetween: 20,
            },
            1024: {
              slidesPerView: 4,
              spaceBetween: 20,
            },
            1280: {
              slidesPerView: 5,
              spaceBetween: 20,
            },
          },
        })

        // Add hover/touch handlers
        let hoverTimeout
        let isHovering = false
        let isTouching = false

        $(swiperEl).on("mouseenter", () => {
          isHovering = true
          horizontalSwiper.autoplay.stop()
          if (hoverTimeout) {
            clearTimeout(hoverTimeout)
            hoverTimeout = null
          }
        })

        $(swiperEl).on("mouseleave", () => {
          isHovering = false
          if (hoverTimeout) clearTimeout(hoverTimeout)

          hoverTimeout = setTimeout(() => {
            if (!isHovering && !isTouching) {
              horizontalSwiper.autoplay.start()
            }
          }, 3000)
        })

        $(swiperEl).on("touchstart", () => {
          isTouching = true
          horizontalSwiper.autoplay.stop()
          if (hoverTimeout) {
            clearTimeout(hoverTimeout)
            hoverTimeout = null
          }
        })

        $(swiperEl).on("touchend", () => {
          isTouching = false
          if (hoverTimeout) clearTimeout(hoverTimeout)

          hoverTimeout = setTimeout(() => {
            if (!isHovering && !isTouching) {
              horizontalSwiper.autoplay.start()
            }
          }, 3000)
        })
      })
    }

    // Category item click handler
    $(document).on("click", ".k-category-item", function () {
      const $item = $(this)
      const categoryId = $item.data("category-id")

      console.log("[v0] Category item clicked:", categoryId)

      // Highlight selected category
      $(".k-category-item").css({
        background: "",
        transform: "",
      })
      $item.css({
        background: "rgba(255, 107, 157, 0.1)",
        transform: "scale(1.05)",
      })

      // Show loading
      $(".k-categories-products-inner").html(
        '<div style="text-align: center; padding: 40px;"><div style="display: inline-block; width: 50px; height: 50px; border: 4px solid #f0f0f0; border-top-color: #ff6b9d; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px; color: #666;">در حال بارگذاری...</p></div>',
      )

      // Add support for "All Products" and "Sales" buttons
      const action = "k_get_category_products"
      const data = {
        action: action,
        category_id: categoryId,
        page: 1,
      }

      // Fetch products
      $.ajax({
        url: window.khoshtipAjax?.ajaxurl || "/wp-admin/admin-ajax.php",
        type: "POST",
        data: data,
        beforeSend: () => {
          console.log("[v0] Sending AJAX request for category:", categoryId)
        },
        success: (response) => {
          console.log("[v0] AJAX response:", response)
          if (response.success) {
            $(".k-categories-products-inner").html(response.data.html)

            // Reinitialize swipers after content loads
            setTimeout(() => {
              reinitializeCategoryPanelSwipers()
            }, 100)

            // If more products exist, add "Load More" button
            if (response.data.has_more) {
              const loadMoreBtn = `
                <div style="text-align: center; padding: 20px;">
                  <button class="k-load-more-products" data-category-id="${categoryId}" data-page="2" 
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px 30px; border-radius: 25px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); transition: all 0.3s ease;">
                    نمایش محصولات بیشتر
                  </button>
                </div>
              `
              $(".k-categories-products-inner").append(loadMoreBtn)
            }
          } else {
            $(".k-categories-products-inner").html(
              '<div style="text-align: center; padding: 40px; color: #e53935;"><p>' +
                (response.data?.message || "خطا در بارگذاری محصولات") +
                "</p></div>",
            )
          }
        },
        error: () => {
          $(".k-categories-products-inner").html(
            '<div style="text-align: center; padding: 40px; color: #e53935;"><p>خطا در برقراری ارتباط با سرور</p></div>',
          )
        },
      })
    })

    // Handler for "Load More" button
    $(document).on("click", ".k-load-more-products", function () {
      const $btn = $(this)
      const categoryId = $btn.data("category-id")
      const page = $btn.data("page")

      $btn
        .html(
          '<span style="display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 1s linear infinite;"></span>',
        )
        .prop("disabled", true)

      $.ajax({
        url: window.khoshtipAjax?.ajaxurl || "/wp-admin/admin-ajax.php",
        type: "POST",
        data: {
          action: "k_get_category_products",
          category_id: categoryId,
          page: page,
        },
        success: (response) => {
          if (response.success) {
            // Extract just the product cards from HTML
            const $responseHtml = $(response.data.html)
            const $productGrid = $responseHtml.filter(".k-category-products-grid")
            const $loadMoreBtn = $responseHtml.find(".k-load-more-products").closest("div")
            
            // Remove current "Load More" button
            $btn.closest("div").remove()

            // Add new products to grid
            if ($productGrid.length) {
              const $grid = $(".k-category-products-grid")
              const $newProducts = $productGrid.find(".k-product-card")
              $grid.append($newProducts)
            } else {
              // If no grid found, try to append product cards directly
              const $grid = $(".k-category-products-grid")
              const $newProducts = $responseHtml.filter(".k-product-card")
              $grid.append($newProducts)
            }

            // If "Load More" button exists in response, append it
            if ($loadMoreBtn.length) {
              $(".k-categories-products-inner").append($loadMoreBtn)
            } else if (response.data.has_more) {
              // Or create new button if has_more is true
              const loadMoreBtn = `
                <div style="text-align: center; padding: 20px;">
                  <button class="k-load-more-products" data-category-id="${categoryId}" data-page="${page + 1}" 
                    style="background: #ff6b9d; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    نمایش محصولات بیشتر ↓
                  </button>
                </div>
              `
              $(".k-categories-products-inner").append(loadMoreBtn)
            }

            // Reinitialize swipers if any exist (for horizontally scrolling layout)
            setTimeout(() => {
              reinitializeCategoryPanelSwipers()
            }, 100)
          }
        },
        error: () => {
          $btn.html("خطا - دوباره تلاش کنید").css("background", "#e53935")
        },
      })
    })

    // Size Search Button Click Handler
    $("#k-search-by-size-btn").on("click", (e) => {
      e.stopPropagation()
      console.log("[v0] Size search button clicked")

      if (typeof window.khoshtipSizeSearch === "undefined" || !window.khoshtipSizeSearch.ajaxurl) {
        console.error("[v0] khoshtipSizeSearch not defined or incomplete:", window.khoshtipSizeSearch)
        alert("خطا در تنظیمات جستجو - لطفاً صفحه رفرش کنید")
        return
      }

      const selectedSizes = []
      $(".k-size-checkbox input[type='checkbox']:checked").each(function () {
        selectedSizes.push($(this).val())
      })

      console.log("[v0] Selected sizes:", selectedSizes)

      if (selectedSizes.length === 0) {
        alert("لطفاً حداقل یک سایز انتخاب کنید")
        return
      }

      // Show loading, hide results
      $("#k-size-search-loading").show()
      $("#k-size-search-results").hide()

      const ajaxData = {
        action: "khoshtip_search_by_size",
        nonce: window.khoshtipSizeSearch.nonce,
        sizes: selectedSizes,
      }

      console.log("[v0] Sending AJAX request:", ajaxData)

      // AJAX request
      $.ajax({
        url: window.khoshtipSizeSearch.ajaxurl,
        type: "POST",
        data: ajaxData,
        success: (response) => {
          console.log("[v0] AJAX Success:", response)
          $("#k-size-search-loading").hide()
          $("#k-size-search-results").show()

          if (response.success) {
            $("#k-size-search-results").html(response.data.html)
            if (response.data.count > 0) {
              // Add count badge
              const countBadge =
                '<div style="text-align: center; margin-bottom: 10px; font-size: 14px; color: #666;"><strong>' +
                response.data.count +
                "</strong> محصول یافت شد</div>"
              $("#k-size-search-results").prepend(countBadge)
            }
          } else {
            $("#k-size-search-results").html(
              '<div style="text-align: center; padding: 20px; color: #e53935;">' +
                (response.data?.message || "خطا در جستجو") +
                "</div>",
            )
          }
        },
        error: (xhr, status, error) => {
          console.error("[v0] AJAX Error:", {
            status: status,
            error: error,
            responseText: xhr.responseText,
            readyState: xhr.readyState,
          })
          $("#k-size-search-loading").hide()
          $("#k-size-search-results")
            .show()
            .html(
              '<div style="text-align: center; padding: 20px; color: #e53935;">خطا در برقراری ارتباط با سرور - جزئیات در console</div>',
            )
        },
      })
    })

    // Search handler for header search button
    $("#k-search-name-btn").on("click", () => {
      const query = $("#k-search-input-name").val().trim()

      if (query.length < 2) {
        $("#k-name-search-results")
          .html(
            '<div style="padding: 20px; text-align: center; color: #e53935;"><p>لطفاً حداقل 2 کاراکتر وارد کنید</p></div>',
          )
          .show()
        return
      }

      // Show loading
      $("#k-name-search-results")
        .html(
          '<div style="text-align: center; padding: 40px;"><div style="display: inline-block; width: 50px; height: 50px; border: 4px solid #f0f0f0; border-top-color: #ff6b9d; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px; color: #666;">در حال جستجو...</p></div>',
        )
        .show()

      $.ajax({
        url: window.khoshtipSizeSearch?.ajaxurl || "/wp-admin/admin-ajax.php",
        type: "POST",
        data: {
          action: "khoshtip_search_products",
          nonce: window.khoshtipSizeSearch?.nonce,
          query: query,
        },
        success: (response) => {
          if (response.success && response.data.html) {
            $("#k-name-search-results").html(response.data.html).show()
          } else {
            $("#k-name-search-results")
              .html(
                '<div style="text-align: center; padding: 40px; color: #999;"><p style="font-size: 16px;">هیچ محصولی یافت نشد</p></div>',
              )
              .show()
          }
        },
        error: () => {
          $("#k-name-search-results")
            .html('<div style="text-align: center; padding: 40px; color: #e53935;"><p>خطا در جستجو</p></div>')
            .show()
        },
      })
    })

    // Trigger search on Enter key press in header search input
    $("#k-search-input-name").on("keypress", (e) => {
      if (e.which === 13) {
        // Enter key
        e.preventDefault()
        $("#k-search-name-btn").click()
      }
    })

    if ($(".k-bottom-bar").length) {
      console.log("[v0] Bottom bar scroll initialized")
      let lastScrollTop = 0
      let inactivityTimer = null
      const scrollThreshold = 5 // Minimum scroll distance to trigger hide/show

      function handleBottomBarScroll() {
        const isMobile = window.innerWidth < 768

        if (!isMobile) {
          // On desktop, always show the bottom bar
          $(".k-bottom-bar").removeClass("k-bottom-bar-hidden")
          $(".k-bottom-bar-wave").removeClass("k-bottom-bar-hidden")
          return
        }

        // Mobile scroll behavior
        const currentScrollTop = $(window).scrollTop()
        const scrollDifference = Math.abs(currentScrollTop - lastScrollTop)

        if (inactivityTimer) {
          clearTimeout(inactivityTimer)
          inactivityTimer = null
        }

        if (scrollDifference > scrollThreshold) {
          if (currentScrollTop > lastScrollTop && currentScrollTop > 100) {
            // Scrolling down - hide both bottom bar and wave
            $(".k-bottom-bar").addClass("k-bottom-bar-hidden")
            $(".k-bottom-bar-wave").addClass("k-bottom-bar-hidden")
          } else {
            // Scrolling up - show both bottom bar and wave
            $(".k-bottom-bar").removeClass("k-bottom-bar-hidden")
            $(".k-bottom-bar-wave").removeClass("k-bottom-bar-hidden")
          }
          lastScrollTop = currentScrollTop
        }

        inactivityTimer = setTimeout(() => {
          console.log("[v0] Inactivity timeout - showing bottom bar")
          $(".k-bottom-bar").removeClass("k-bottom-bar-hidden")
          $(".k-bottom-bar-wave").removeClass("k-bottom-bar-hidden")
        }, 2000)
      }

      $(window).on("scroll", handleBottomBarScroll)

      $(window).on("resize", () => {
        handleBottomBarScroll()
      })
    } else {
      console.log("[v0] Bottom bar not found on page")
    }

    // Countdown Timer
    function initCountdown() {
      const countdownEl = $("#countdown-timer")
      if (countdownEl.length === 0) return

      const targetTimestamp = Number.parseInt(countdownEl.data("target-timestamp"))

      if (!targetTimestamp || isNaN(targetTimestamp)) {
        console.error("[v0] Invalid countdown timestamp")
        return
      }

      let intervalId

      function updateCountdown() {
        const now = new Date().getTime()
        const distance = targetTimestamp - now

        console.log("[v0] Timer update - Distance:", distance, "Target:", targetTimestamp, "Now:", now)

        if (distance < 0) {
          console.log("[v0] Timer expired - hiding sales section")
          $(".sales-section").fadeOut(500, function () {
            localStorage.setItem("k_sale_ended_at", targetTimestamp)
            $(this).remove()
          })
          if (intervalId) {
            clearInterval(intervalId)
            intervalId = null
          }
          return
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24))
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))
        const seconds = Math.floor((distance % (1000 * 60)) / 1000)

        if (days > 0) {
          $(".countdown-days-wrapper").show()
          $(".countdown-days").text(String(days).padStart(2, "0"))
        } else {
          $(".countdown-days-wrapper").hide()
        }

        $(".countdown-hours").text(String(hours).padStart(2, "0"))
        $(".countdown-minutes").text(String(minutes).padStart(2, "0"))
        $(".countdown-seconds").text(String(seconds).padStart(2, "0"))
      }

      const saleEndedAt = localStorage.getItem("k_sale_ended_at")
      if (saleEndedAt && Number.parseInt(saleEndedAt) === targetTimestamp) {
        console.log("[v0] Sale already ended in previous session - hiding immediately")
        $(".sales-section").hide().remove()
        return
      }

      updateCountdown()
      intervalId = setInterval(updateCountdown, 1000)
    }

    initCountdown()

    $("[data-accordion-trigger]").on("click", function () {
      const $button = $(this)
      const $item = $button.closest(".accordion-item")
      const $content = $item.find("[data-accordion-content]")
      const $icon = $button.find(".accordion-icon")
      const isActive = $button.hasClass("active")

      if (isActive) {
        // Close current accordion
        $button.removeClass("active")
        $content.slideUp(300)
        $icon.css("transform", "rotate(0deg)")
      } else {
        // Close all other accordions
        $("[data-accordion-trigger]").not($button).removeClass("active")
        $("[data-accordion-trigger]").not($button).find(".accordion-icon").css("transform", "rotate(0deg)")
        $("[data-accordion-content]").not($content).slideUp(300)

        // Open current accordion
        $button.addClass("active")
        $content.slideDown(300, () => {
          // Initialize Swiper after accordion opens
          initAccordionSwiper($content)
        })
        $icon.css("transform", "rotate(180deg)")
      }
    })

    // Initialize Hero Swiper
    if ($(".hero-swiper").length && typeof Swiper !== "undefined") {
      new Swiper(".hero-swiper", {
        loop: true,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
      })
    }

    // Initialize Sales Swiper with custom hover behavior
    if ($(".sales-swiper").length && typeof Swiper !== "undefined") {
      const salesSwiper = new Swiper(".sales-swiper", {
        slidesPerView: 2,
        spaceBetween: 16,
        loop: true,
        speed: 3000,
        autoplay: {
          delay: 0,
          disableOnInteraction: false,
        },
        freeMode: true,
        freeModeMomentum: false,
        breakpoints: {
          640: {
            slidesPerView: 2.5,
            spaceBetween: 20,
          },
          768: {
            slidesPerView: 3,
            spaceBetween: 20,
          },
          1024: {
            slidesPerView: 4,
            spaceBetween: 20,
          },
          1280: {
            slidesPerView: 5,
            spaceBetween: 20,
          },
        },
      })

      let hoverTimeout
      let isHovering = false
      let isTouching = false

      $(".sales-swiper").on("mouseenter", () => {
        isHovering = true
        salesSwiper.autoplay.stop()
        if (hoverTimeout) {
          clearTimeout(hoverTimeout)
          hoverTimeout = null
        }
      })

      $(".sales-swiper").on("mouseleave", () => {
        isHovering = false
        if (hoverTimeout) clearTimeout(hoverTimeout)

        hoverTimeout = setTimeout(() => {
          if (!isHovering && !isTouching) {
            salesSwiper.autoplay.start()
          }
        }, 3000)
      })

      $(".sales-swiper").on("touchstart", () => {
        isTouching = true
        salesSwiper.autoplay.stop()
        if (hoverTimeout) {
          clearTimeout(hoverTimeout)
          hoverTimeout = null
        }
      })

      $(".sales-swiper").on("touchend", () => {
        isTouching = false
        if (hoverTimeout) clearTimeout(hoverTimeout)

        hoverTimeout = setTimeout(() => {
          if (!isHovering && !isTouching) {
            salesSwiper.autoplay.start()
          }
        }, 3000)
      })

      $(".sales-swiper").on("touchcancel", () => {
        isTouching = false
        if (hoverTimeout) clearTimeout(hoverTimeout)

        hoverTimeout = setTimeout(() => {
          if (!isHovering && !isTouching) {
            salesSwiper.autoplay.start()
          }
        }, 3000)
      })
    }

    if (typeof Swiper !== "undefined") {
      const $firstAccordionContent = $(".accordion-item:first-child [data-accordion-content]")
      if ($firstAccordionContent.length && $firstAccordionContent.is(":visible")) {
        initAccordionSwiper($firstAccordionContent)
      }
    }

    // Initialize Latest Products Swiper
    if ($(".latest-products-section .latest-products-swiper").length && typeof Swiper !== "undefined") {
      const $latestSection = $(".latest-products-section")
      if ($latestSection.find(".accordion-item").hasClass("active")) {
        // Rotate icon to show it's open
        $latestSection.find(".accordion-icon").css("transform", "rotate(180deg)")
      }
    }

    // Initialize Blog Swiper (Mobile)
    if ($(".blog-swiper").length && typeof Swiper !== "undefined") {
      new Swiper(".blog-swiper", {
        slidesPerView: 2,
        spaceBetween: 15,
        centeredSlides: false,
        breakpoints: {
          640: {
            slidesPerView: 2.5,
          },
        },
      })
    }

    // Initialize Accordion Swiper with custom hover behavior
    function initAccordionSwiper($content) {
      if (typeof Swiper === "undefined") return

      // Get all swipers in this accordion content
      const swipers = $content.find(".swiper")
      
      swipers.each(function() {
        const swiperEl = this
        
        // Destroy existing swiper if it exists
        if (swiperEl.swiper) {
          swiperEl.swiper.destroy(true, true)
        }
        
        const accordionSwiper = new Swiper(swiperEl, {
          slidesPerView: 2,
          spaceBetween: 16,
          loop: true,
          speed: 3000,
          autoplay: {
            delay: 0,
            disableOnInteraction: false,
          },
          freeMode: true,
          freeModeMomentum: false,
          breakpoints: {
            640: {
              slidesPerView: 2.5,
              spaceBetween: 20,
            },
            768: {
              slidesPerView: 3,
              spaceBetween: 20,
            },
            1024: {
              slidesPerView: 4,
              spaceBetween: 20,
            },
            1280: {
              slidesPerView: 5,
              spaceBetween: 20,
            },
          },
        })

        let hoverTimeout
        let isHovering = false
        let isTouching = false

        $(swiperEl).on("mouseenter", () => {
          isHovering = true
          accordionSwiper.autoplay.stop()
          if (hoverTimeout) {
            clearTimeout(hoverTimeout)
            hoverTimeout = null
          }
        })

        $(swiperEl).on("mouseleave", () => {
          isHovering = false
          if (hoverTimeout) clearTimeout(hoverTimeout)

          hoverTimeout = setTimeout(() => {
            if (!isHovering && !isTouching) {
              accordionSwiper.autoplay.start()
            }
          }, 3000)
        })

        $(swiperEl).on("touchstart", () => {
          isTouching = true
          accordionSwiper.autoplay.stop()
          if (hoverTimeout) {
            clearTimeout(hoverTimeout)
            hoverTimeout = null
          }
        })

        $(swiperEl).on("touchend", () => {
          isTouching = false
          if (hoverTimeout) clearTimeout(hoverTimeout)

          hoverTimeout = setTimeout(() => {
            if (!isHovering && !isTouching) {
              accordionSwiper.autoplay.start()
            }
          }, 3000)
        })

        $(swiperEl).on("touchcancel", () => {
          isTouching = false
          if (hoverTimeout) clearTimeout(hoverTimeout)

          hoverTimeout = setTimeout(() => {
            if (!isHovering && !isTouching) {
              accordionSwiper.autoplay.start()
            }
          }, 3000)
        })
      })
    }

    function updateCartCount() {
      $.ajax({
        url: window.khoshtipAjax?.ajaxurl || "/wp-admin/admin-ajax.php",
        type: "POST",
        data: {
          action: "k_get_cart_count",
        },
        success: (response) => {
          if (response.success && response.data.count !== undefined) {
            const count = response.data.count
            if (count > 0) {
              $(".k-cart-count").text(count).show()
            } else {
              $(".k-cart-count").hide()
            }
          }
        },
      })
    }

    // Update cart count on page load
    updateCartCount()

    // Update cart count when products are added to cart
    $(document.body).on("added_to_cart", () => {
      updateCartCount()
    })

    // Update cart count periodically (every 5 seconds)
    setInterval(updateCartCount, 5000)
  })
})()
