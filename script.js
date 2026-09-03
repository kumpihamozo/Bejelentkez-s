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
return email.includes("@");
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

// REGISTER
document.getElementById("registerForm")
.addEventListener("submit",e=>{

e.preventDefault();

let name=document.getElementById("regName");
let email=document.getElementById("regEmail");
let pass=document.getElementById("regPassword");
let pass2=document.getElementById("regPassword2");
let terms=document.getElementById("terms");
let newsletter=document.getElementById("newsletter");

let pwError=document.getElementById("passwordError");
pwError.innerText="";

let users=JSON.parse(localStorage.getItem("users"))||[];

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

// duplicate email
if(users.some(u=>u.email===email.value)){

message.style.color="red";
message.innerText="❌ Ez az email már létezik";
return;

}

// save user
users.push({
name:name.value,
email:email.value,
password:pass.value,
newsletter:newsletter.checked
});

localStorage.setItem("users",JSON.stringify(users));

message.style.color="green";
message.innerText="✅ Sikeres regisztráció";

e.target.reset();

});

// LOGIN
document.getElementById("loginForm")
.addEventListener("submit",e=>{

e.preventDefault();

let email=document.getElementById("loginEmail");
let pass=document.getElementById("loginPassword");

let users=JSON.parse(localStorage.getItem("users"))||[];

let user=users.find(u=>
u.email===email.value &&
u.password===pass.value
);

if(!user){

message.style.color="red";
message.innerText="❌ Hibás email vagy jelszó";
return;

}

message.style.color="green";
message.innerText="✅ Üdv "+user.name;

e.target.reset();

});

// FORGOT PASSWORD
document.getElementById("forgotPassword")
.addEventListener("click",()=>{

let email=prompt("Add meg az email címed:");

if(!email){
alert("Nem adtál meg emailt");
return;
}

let users=JSON.parse(localStorage.getItem("users"))||[];

let index=users.findIndex(u=>u.email===email);

if(index===-1){
alert("Nincs ilyen felhasználó");
return;
}

let newPass=prompt("Add meg az új jelszót (min 8 karakter):");

if(!newPass||newPass.length<8){
alert("Túl rövid jelszó");
return;
}

users[index].password=newPass;

localStorage.setItem("users",JSON.stringify(users));

alert("Sikeres jelszó módosítás");
});