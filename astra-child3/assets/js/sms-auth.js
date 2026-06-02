;(($) => {
  // Declare jQuery and khoshtipAuth variables
  const jQuery = window.jQuery
  const khoshtipAuth = window.khoshtipAuth

  let resendTimer = null
  let resendSeconds = 0

  function startResendTimer(seconds) {
    console.log("[v0] Starting resend timer for", seconds, "seconds") // Added debug log
    resendSeconds = seconds
    const $resendBtn = $("#resendCodeBtn")
    $resendBtn.prop("disabled", true)

    resendTimer = setInterval(() => {
      resendSeconds--
      $resendBtn.html(`ارسال مجدد کد (<span class="resend-timer">${resendSeconds}</span>)`)
      console.log("[v0] Timer tick:", resendSeconds) // Added debug log

      if (resendSeconds <= 0) {
        clearInterval(resendTimer)
        $resendBtn.prop("disabled", false).text("ارسال مجدد کد")
        console.log("[v0] Timer completed, button enabled") // Added debug log
      }
    }, 1000)
  }

  function stopResendTimer() {
    if (resendTimer) {
      clearInterval(resendTimer)
      resendTimer = null
    }
    $("#resendCodeBtn").prop("disabled", false).text("ارسال مجدد کد")
  }

  jQuery(document).ready(($) => {
    // Open SMS login modal
    $("#smsLoginBtn").on("click", () => {
      $("#smsLoginModal").fadeIn()
      $("#smsPhone").focus()
    })

    // Send SMS code
    $("#sendCodeBtn").on("click", function () {
      const phone = $("#smsPhone").val().trim()
      const $btn = $(this)
      const $error = $("#smsError")

      // Validate phone number
      if (!/^09[0-9]{9}$/.test(phone)) {
        $error.text("شماره موبایل نامعتبر است").fadeIn()
        return
      }

      $error.hide()
      $btn.prop("disabled", true).text("در حال ارسال...")

      $.ajax({
        url: khoshtipAuth.ajaxurl,
        type: "POST",
        data: {
          action: "send_sms_code",
          nonce: khoshtipAuth.smsNonce,
          phone: phone,
        },
        success: (response) => {
          if (response.success) {
            $("#smsStep1").hide()
            $("#smsStep2").show()
            $("#displayPhone").text(phone)
            $("#smsCode").focus()
            console.log("[v0] SMS sent successfully, starting timer") // Added debug log
            startResendTimer(30) // Changed timer from 60 to 30 seconds
          } else {
            $error.text(response.data.message || "خطا در ارسال کد").fadeIn()
          }
        },
        error: () => {
          $error.text("خطا در ارتباط با سرور").fadeIn()
        },
        complete: () => {
          $btn.prop("disabled", false).text("ارسال کد تایید")
        },
      })
    })

    // Verify SMS code
    $("#verifyCodeBtn").on("click", function () {
      const phone = $("#smsPhone").val().trim()
      const code = $("#smsCode").val().trim()
      const $btn = $(this)
      const $error = $("#smsError2")

      if (code.length !== 6) {
        $error.text("کد تایید باید 6 رقم باشد").fadeIn()
        return
      }

      $error.hide()
      $btn.prop("disabled", true).text("در حال تایید...")

      $.ajax({
        url: khoshtipAuth.ajaxurl,
        type: "POST",
        data: {
          action: "verify_sms_code",
          nonce: khoshtipAuth.smsNonce,
          phone: phone,
          code: code,
        },
        success: (response) => {
          if (response.success) {
            stopResendTimer()
            window.location.href = response.data.redirect
          } else {
            $error.text(response.data.message || "کد تایید نادرست است").fadeIn()
          }
        },
        error: () => {
          $error.text("خطا در ارتباط با سرور").fadeIn()
        },
        complete: () => {
          $btn.prop("disabled", false).text("تایید و ورود")
        },
      })
    })

    $("#resendCodeBtn").on("click", function () {
      console.log("[v0] Resend button clicked, disabled:", $(this).prop("disabled")) // Added debug log

      // Prevent clicking while timer is active
      if ($(this).prop("disabled")) {
        console.log("[v0] Button is disabled, ignoring click") // Added debug log
        return
      }

      const phone = $("#smsPhone").val().trim()
      const $btn = $(this)
      const $error = $("#smsError2")

      $error.hide()
      $btn.prop("disabled", true).text("در حال ارسال...")

      $.ajax({
        url: khoshtipAuth.ajaxurl,
        type: "POST",
        data: {
          action: "send_sms_code",
          nonce: khoshtipAuth.smsNonce,
          phone: phone,
        },
        success: (response) => {
          if (response.success) {
            console.log("[v0] Code resent successfully, restarting timer") // Added debug log
            startResendTimer(30)
            $error.text("کد مجدداً ارسال شد").css("color", "green").fadeIn()
            setTimeout(() => $error.fadeOut(), 3000)
          } else {
            $error.text(response.data.message || "خطا در ارسال مجدد کد").fadeIn()
            $btn.prop("disabled", false).text("ارسال مجدد کد")
          }
        },
        error: () => {
          $error.text("خطا در ارتباط با سرور").fadeIn()
          $btn.prop("disabled", false).text("ارسال مجدد کد")
        },
      })
    })

    // Enter key handlers
    $("#smsPhone").on("keypress", (e) => {
      if (e.which === 13) {
        $("#sendCodeBtn").click()
      }
    })

    $("#smsCode").on("keypress", (e) => {
      if (e.which === 13) {
        $("#verifyCodeBtn").click()
      }
    })
  })

  window.closeSmsModal = () => {
    stopResendTimer()
    jQuery("#smsLoginModal").fadeOut()
    jQuery("#smsStep1").show()
    jQuery("#smsStep2").hide()
    jQuery("#smsPhone").val("")
    jQuery("#smsCode").val("")
    jQuery("#smsError, #smsError2").hide()
  }
})(window.jQuery)
