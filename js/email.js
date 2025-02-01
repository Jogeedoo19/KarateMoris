

    (function(){
        emailjs.init("A8Qz5MBh2LG--95fD"); 
    })();

    function sendEmail() {
        emailjs.send("service_y4w6hsn", "template_u4qoo3c", {
            email: document.getElementById("email").value, 
            username: document.getElementById("username").value, 
             reply_to: "jogeedooshakshi@gmail.com"
        }).then(
            function(response) {
                alert("Email sent successfully!");
                console.log("Email Sent:", response);
            },
            function(error) {
                alert("Email failed to send: " + error.text);
                console.error("Error:", error);
            }
        );
    }

