const tabs=document.querySelectorAll(".tab");
const forms=document.querySelectorAll(".form");
const message=document.getElementById("message");

// TAB SWITCH
tabs.forEach(tab=>{
tab.addEventListener("click",()=>{

tabs.forEach(t=>t.classList.remove("active"));
forms.forEach(f=>f.classList.remove("active"));

tab.classList.add("active");
document.getElementById(tab.dataset.form).classList.add("active");

message.innerText="";

});
});

// PASSWORD SHOW/HIDE
document.querySelectorAll(".eye").forEach(eye=>{
eye.addEventListener("click",()=>{

let input=eye.previousElementSibling;
input.type=input.type==="password"?"text":"password";

});
});

// EMAIL CHECK
function validEmail(email){
return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// PASSWORD CHECK (bővített speciális karakterek)
function passwordCheck(password){

if(password.length<8)
return "Minimum 8 karakter";

if(!/[A-Z]/.test(password))
return "Kell nagybetű";

if(!/[a-z]/.test(password))
return "Kell kisbetű";

if(!/\d/.test(password))
return "Kell szám";

if(!/[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?`~]/.test(password))
return "Kell speciális karakter";

return "";
}

async function sendAuthRequest(data){
const response=await fetch("service.php",{
method:"POST",
body:new URLSearchParams(data)
});
const result=await response.json();
if(!response.ok){
throw new Error(result.message||"A kérés nem sikerült");
}
return result;
}

// REGISTER
document.getElementById("registerForm")
.addEventListener("submit",async e=>{

e.preventDefault();

let name=document.getElementById("regName");
let email=document.getElementById("regEmail");
let pass=document.getElementById("regPassword");
let pass2=document.getElementById("regPassword2");
let terms=document.getElementById("terms");
let newsletter=document.getElementById("newsletter");

let pwError=document.getElementById("passwordError");
pwError.innerText="";

name.value=name.value.trim();
email.value=email.value.trim().toLowerCase();

// empty check
if(!name.value||!email.value||!pass.value||!pass2.value){

message.style.color="red";
message.innerText="❌ Tölts ki minden mezőt";
return;

}

// email
if(!validEmail(email.value)){

message.style.color="red";
message.innerText="❌ Hibás email (kell @)";
return;

}

// password rules
let error=passwordCheck(pass.value);

if(error){
pwError.innerText=error;
return;
}

// match
if(pass.value!==pass2.value){

message.style.color="red";
message.innerText="❌ A jelszavak nem egyeznek";
return;

}

// terms
if(!terms.checked){

message.style.color="red";
message.innerText="❌ Fogadd el a feltételeket";
return;

}

try{
const result=await sendAuthRequest({action:"register",name:name.value,email:email.value,password:pass.value,newsletter:newsletter.checked?"1":"0"});
message.style.color="green";
message.innerText=result.message;
e.target.reset();
}catch(error){
message.style.color="red";
message.innerText=error.message;
}

});

// LOGIN
document.getElementById("loginForm")
.addEventListener("submit",async e=>{

e.preventDefault();

let email=document.getElementById("loginEmail");
let pass=document.getElementById("loginPassword");

email.value=email.value.trim().toLowerCase();

try{
const result=await sendAuthRequest({action:"login",email:email.value,password:pass.value});

message.style.color="green";
message.innerText=result.message;
e.target.reset();
}catch(error){
message.style.color="red";
message.innerText=error.message;
}

});

// FORGOT PASSWORD
document.getElementById("forgotPassword")
.addEventListener("click",()=>{
message.style.color="red";
message.innerText="A jelszó-visszaállításhoz email-küldő szolgáltatás szükséges.";
});