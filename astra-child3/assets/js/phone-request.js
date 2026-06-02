/**
 * مدیریت درخواست شماره موبایل از کاربران
 */

;(($) => {
  console.log("[v0] Phone request script initializing...")

  const phoneCheck = window.khoshtipPhoneCheck

  if (typeof phoneCheck === "undefined") {
    console.log("[v0] khoshtipPhoneCheck not defined - user may not be logged in")
    return
  }

  console.log("[v0] Phone check data:", phoneCheck)

  // ثبت شماره موبایل
  $(document).on("click", "#submit-phone-request", function (e) {
    e.preventDefault()

    const $button = $(this)
    const $input = $("#phone-request-input")
    const $error = $(".phone-request-error")
    const phone = $input.val().trim()

    console.log("[v0] Submit phone button clicked, phone value:", phone)

    // اعتبارسنجی شماره موبایل
    const phoneRegex = /^09[0-9]{9}$/
    if (!phoneRegex.test(phone)) {
      $error.text("لطفاً شماره موبایل معتبر وارد کنید (مثال: 09123456789)").fadeIn()
      return
    }

    $error.hide()
    $button.prop("disabled", true).text("در حال ثبت...")

    console.log("[v0] Submitting phone number via AJAX...")

    $.ajax({
      url: phoneCheck.ajaxurl,
      type: "POST",
      data: {
        action: "save_user_phone",
        nonce: phoneCheck.nonce,
        phone: phone,
      },
      success: (response) => {
        console.log("[v0] Phone save response:", response)

        if (response.success) {
          console.log("[v0] Phone saved successfully! Closing modal...")
          $button.text("✓ ثبت شد!")
          setTimeout(() => {
            $("#phone-request-modal").removeClass("show").fadeOut(300)
          }, 800)
        } else {
          $error.text(response.data || "خطا در ثبت شماره موبایل").fadeIn()
          $button.prop("disabled", false).text("ثبت شماره موبایل")
        }
      },
      error: (xhr, status, error) => {
        console.error("[v0] Phone save AJAX error:", {
          status: status,
          error: error,
          response: xhr.responseText,
        })
        $error.text("خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.").fadeIn()
        $button.prop("disabled", false).text("ثبت شماره موبایل")
      },
    })
  })

  // فقط اعداد در فیلد شماره موبایل
  $(document).on("input", "#phone-request-input", function () {
    this.value = this.value.replace(/[^0-9]/g, "")
  })

  // Enter key handler
  $(document).on("keypress", "#phone-request-input", (e) => {
    if (e.which === 13) {
      $("#submit-phone-request").click()
    }
  })

  $(document).ready(() => {
    console.log("[v0] Document ready, checking if phone needed...")
    console.log("[v0] needsPhone value:", phoneCheck.needsPhone)

    if (phoneCheck.needsPhone === "true") {
      console.log("[v0] User needs to add phone! Showing modal in 500ms...")
      setTimeout(() => {
        $("#phone-request-modal").addClass("show").fadeIn(300)
      }, 500)
    } else {
      console.log("[v0] User already has phone number")
    }
  })

  console.log("[v0] Phone request script loaded successfully")
})(window.jQuery)
