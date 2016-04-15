var email = {};

// email[0] = 'email1@gmail.com';
email[0] = 'email1@gmail.com, email2@gmail.com';


var email_arr = email[0].split(',');

// email_arr = {
// 	0:'email1@gmail.com',
// 	// 1:'email2@gmail.com'
// }

for(var i = 0; i >= email_arr.length, i++){
	var myEmail = email_arr[i];
	MailApp.sendEmail(myEmail, subject, text);	
}



