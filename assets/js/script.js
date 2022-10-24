jQuery.validator.addMethod("fullname", function (value, element) {
  if (
    /^([a-zA-Z]{2,}\s[a-zA-z]{1,}'?-?[a-zA-Z]{2,}\s?([a-zA-Z]{1,})?)/.test(
      value
    )
  ) {
    return true;
  } else {
    return false;
  }
});

jQuery.validator.addMethod("phoneKS", function (phone_number, element) {
  phone_number = phone_number.replace(/\s+/g, "");
  return (
    this.optional(element) ||
    (phone_number.length == 9 &&
      phone_number.match(/^\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{3})/))
  );
});

$("#register").validate({
  rules: {
    fullName: {
      required: true,
      fullname: true,
      minlength: 5,
      maxlength: 30,
    },
    email: {
      required: true,
      email: true,
      remote: {
        url: "assets/js/valid-emails.php",
        type: "get",
        data: {
          email: function () {
            return $("#email").val();
          },
        },
      },
    },
    phone: {
      required: true,
      phoneKS: true,
    },
    password: {
      required: true,
      minlength: 8,
    },
  },
  messages: {
    fullName: {
      required: "Full name is required",
      fullname: "Please enter a valid string",
      minlength: "Please enter a valid name",
      maxlength: "This name is too long",
    },
    email: {
      required: "We need your email address to contact you",
      email: "Your email address must be in the format of name@domain.com",
      remote: "An user with this email address already exists",
    },
    phone: {
      required: "We need your phone number",
      phoneKS: "Please enter a valid phone number",
    },
    password: {
      required: "We need your password",
      minlength: "Password must be at least 8 characters",
    },
  },
});

$("#login").validate({
  rules: {
    email: {
      required: true,
      email: true,
    },
    password: {
      required: true,
    },
  },
  messages: {
    email: {
      required: "We need your email address to contact you",
    },
    password: {
      required: "We need your password",
    },
  },
});

$('[name="accountType"]').on("change", function () {
  if ($(this).attr("id") == "personal") {
    $("#accountType").text("Full Name");
  } else {
    $("#accountType").text("Business Name");
  }
});
