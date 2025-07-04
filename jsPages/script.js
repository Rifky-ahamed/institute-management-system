
//check signup details a given in right format
document.addEventListener("DOMContentLoaded",function(){
    const form = document.getElementById("myForm-signup");

    form.addEventListener("submit",function(event){
        event.preventDefault();

        const instituteName = document.getElementById("instituteName").value.trim();
        const password = document.getElementById("password").value.trim();
        
        const email = document.getElementById("email").value.trim();
        const confirmPassword = document.getElementById("confirmPassword").value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        

        if(instituteName == "" || email =="" || password == "" || confirmPassword == ""){
            alert("Please enter data to fields");
            return false;
        }

        if(!emailPattern.test(email)){
            document.getElementById('email').value = ""; // clear invalid email
            document.getElementById('email').placeholder = "Please enter a valid email address" ;
            return false ;
        }

       

        if(password !== confirmPassword){
            document.getElementById('confirmPassword').value = "";
            document.getElementById('confirmPassword').placeholder = "Please check your password" ;
            return false ;
        }

        // If all validations pass
        alert("Form submitted successfully!");
        form.submit(); // or perform your own logic

    });
});



