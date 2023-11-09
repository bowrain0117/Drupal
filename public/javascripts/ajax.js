 $(document).ready(function() {

    // Handle form submission

    $("#submit-button").click(function(e) {

       

      e.preventDefault(); // Prevent the default form submission behavior



      // Serialize the form data

       var formAction = $(this).attr("action");

      var formData = $("#payment-form").serialize();

  

      $.ajax({

        type: "POST",

        url: 'https://www.uniballink.com/javascripts/process_form.php',

        data: formData,

        success: function(response) {

          if (response === "Data inserted successfully") {

            // Display success message

            Swal.fire({

              icon: "success",

              title: "Form submitted successfully",

              showConfirmButton: false,

              timer: 1500

            });

            // Call your Stripe payment function here

            // Example: initiateStripePayment();

          } else {

            // Display error message

            Swal.fire({

              icon: "error",

              title: "Error during form submission",

              text: response

            });

          }

        },

        error: function(xhr, status, error) {

          // Display error message for AJAX failure

          Swal.fire({

            icon: "error",

            title: "AJAX request failed",

            text: "Status: " + status

          });

        }

      });

    });



    // Define your Stripe payment integration function here

    function initiateStripePayment() {

      // Use Stripe.js to handle payment processing

      // Example: Stripe.payment.createToken(...);

    }

  });