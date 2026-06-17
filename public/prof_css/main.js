// GENERATED JAVASCRIPT:

const profile = document.getElementById("profile");
const edit = document.getElementById("edit");
const gi = document.getElementById("gi");
const la = document.getElementById("la");
const insurer = document.getElementById("insurer");
const regulator = document.getElementById("regulator");
const ipec = document.getElementById("ipec");
const logout = document.getElementById("logout");
const start = document.getElementById("start");
const log_in = document.getElementById('log_in');
const sign_up = document.getElementById('sign_up');
const ib = document.getElementById('ib');
const broking_slip = document.getElementById("broking_slip");
const si = document.getElementById("si");
const accounts_statement = document.getElementById("accounts_statement");
const register_slip = document.getElementById('register_slip');
const register_client = document.getElementById('register_client');
const register_insurer = document.getElementById('register_insurer');
const register_vehicle = document.getElementById('register_vehicle');
const register_claim = document.getElementById('register_claim');
const register_expenses = document.getElementById('register_expenses');
const view_statement = document.getElementById('view_statement');



const currentURL = () => window.location.href;

//LOGIN BUTTON
function logIn() {
  return window.location.assign("/login/index.php");
}
// ADD THE EVENT LISTENER
if (log_in) {

    log_in.addEventListener("click", logIn);
}

//SIGN UP BUTTON
function signUp() {
  return window.location.assign("/register/index.php");
}
// ADD THE EVENT LISTENER
if (sign_up) {

    sign_up.addEventListener("click", signUp);
}

//EDIT PROFILE BUTTON
function editProfile() {
  return window.location.assign("#");
}

// ADD THE EVENT LISTENER
if (profile) {

    profile.addEventListener("click", editProfile);
}




//GENERAL INSURANCE BUTTON
function generalInsurance() {

   return window.location.assign("/general_insurance/index.php");
}

// ADD THE EVENT LISTENER
if (gi) {
    gi.addEventListener("click", generalInsurance);
 } 

//INSURER BUTTON
function insuRer() {
   return window.location.assign("/general_insurance/index.php");
}

// ADD THE EVENT LISTENER
if (insurer) {
    insurer.addEventListener("click", insuRer);
 } 

 //REGULATOR BUTTON
function reguLator() {
   return window.location.assign("/general_insurance/index.php");
}

// ADD THE EVENT LISTENER
if (regulator) {
    regulator.addEventListener("click", reguLator);
 } 

//LOGOUT BUTTON
function logOut() {
   return window.location.assign("/logout/index.php");
}

// ADD THE EVENT LISTENER
if (logout) {
    logout.addEventListener("click", logOut);
 } 


//EDIT ACCOUNT BUTTON
function eDit() {
   return window.location.assign("/edit_account/index.php");
}

// ADD THE EVENT LISTENER
if (edit) {
    edit.addEventListener("click", eDit);
 } 

//INSURANCE BROKING BUTTON
function insuranceBroking() {
   return window.location.assign("/insurance_broking/index.php?action=insurance_broking");
}
// ADD THE EVENT LISTENER
if (ib) {
ib.addEventListener("click", insuranceBroking);
}


//SMART INVOICE BUTTON
function smartInvoice() {
   return window.location.assign("/insurance_broking/smart_invoice/index.php?");
}

// ADD THE EVENT LISTENER
if (si) {
    si.addEventListener("click", smartInvoice);
 } 

//SLIP REGISTER BUTTON
function slipRegister() {
   return window.location.assign("/insurance_broking/register/index.php?action=register_slip");
}

// ADD THE EVENT LISTENER
if (register_slip) {
    register_slip.addEventListener("click", slipRegister);
 } 



//CLIENT REGISTER BUTTON
function clientRegister() {
   return window.location.assign("/insurance_broking/register/index.php?action=register_client");
}

// ADD THE EVENT LISTENER
if (register_client) {
    register_client.addEventListener("click", clientRegister);
 }

//INSURER REGISTER BUTTON
function insurerRegister() {
   return window.location.assign("/insurance_broking/register/index.php?action=register_insurer");
}

// ADD THE EVENT LISTENER
if (register_insurer) {
    register_insurer.addEventListener("click", insurerRegister);
 }

//VEHICLE REGISTER BUTTON
function vehicleRegister() {
   return window.location.assign("/insurance_broking/register/index.php?action=register_vehicle");
}

// ADD THE EVENT LISTENER
if (register_vehicle) {
    register_vehicle.addEventListener("click", vehicleRegister);
 }


//CLAIM REGISTER BUTTON
function claimRegister() {
   return window.location.assign("/insurance_broking/register/index.php?action=register_claim");
}

// ADD THE EVENT LISTENER
if (register_claim) {
    register_claim.addEventListener("click", claimRegister);
 }

//EXPENSE REGISTER BUTTON
function registerExpenses() {
  return window.location.assign("/insurance_broking/register/index.php?action=register_expenses");
}

// ADD THE EVENT LISTENER
if (register_expenses) {

    register_expenses.addEventListener("click", registerExpenses);
}

//VIEW STATEMENT
function viewStatement() {
  return window.location.assign("/insurance_broking/accounts/statements/view_statement/index.php");
}

// ADD THE EVENT LISTENER
if (view_statement) {

    view_statement.addEventListener("click", viewStatement);
}

