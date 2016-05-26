

$(document).ready(function() {
    $("#submit_btn").click(function() { 
    	
    				 var name = document.getElementById('name').value;
					   var email = document.getElementById('email').value;
					   var message = document.getElementById('message').value;
					   var societe = document.getElementById('societe').value;
					   var fichier = document.getElementById('fichier').value;
					   
					   // CHECK FILE -- on commence par en bas pour que le onfocus replace la page au bon niveau d'erreur
					   if(fichier != "" ){ 
					      document.getElementById('fichier').style.border = '1px solid #CFD8E0'; // on remet la couleur du border du INPUT
					      document.getElementById('fichierbox').innerHTML = '&nbsp;<img src="images\/check_icon.png" alt="" \/>'; // on affiche une image OK
					   }
					   
					   // CHECK MESSAGE 
					   if(message == "" || message.length<7){ // si champ comment vide
					      document.getElementById('message').focus(); // focus dans le champ
					      document.getElementById('messagebox').innerHTML = '<img src="images\/icone_alerte.png" alt="" \/>'; 
					      proceed = false;
					   }else{ // si champ comment rempli
					      document.getElementById('messagebox').innerHTML = '&nbsp;<img src="images\/check_icon.png" alt="" \/>'; // on affiche une image OK
					   }
					   
					   	//check champs obligatoires
					   	var proceed = true;	
							$("#contact_form input[required=true], #contact_form textarea[required=true]").each(function(){
								$(this).css('border-color',''); 
								if(!$.trim($(this).val())){ //if this field is empty 
									$(this).css('border-color','red');   
									proceed = false; 
									document.getElementById('contact_results').style = 'margin-bottom:30px'; // on remet la couleur du border du INPUT
								}
								
								//check invalid email
								var email_reg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/; 
								
								if($(this).attr("type")=="email" && !email_reg.test($.trim($(this).val())) || email == ""){  
									document.getElementById('email').style.border = '1px solid #D41919';
									document.getElementById('emailbox').innerHTML = '<img src="images\/icone_alerte.png" alt="" \/>';	
								}else if($(this).attr("type")=="email" && email_reg.test($.trim($(this).val()))){
					      	document.getElementById('emailbox').innerHTML = '&nbsp;<img src="images\/check_icon.png" alt="" \/>'; // on affiche une image OK
								}
							});
					   	
					   // CHECK societe
					   if(societe != "" ){ 
					      document.getElementById('societebox').innerHTML = '&nbsp;<img src="images\/check_icon.png" alt="" \/>'; // on affiche une image OK
					   }
					   
					   // CHECK NOM
					   if(name == "" || name.length<4){
					      document.getElementById('name').style.border = '1px solid #D41919';
					      document.getElementById('namebox').innerHTML = '<img src="images\/icone_alerte.png" alt="" \/>';
					      proceed = false;
					   }else{
					      document.getElementById('name').style.border = '1px solid #CFD8E0';
					      document.getElementById('namebox').innerHTML = '&nbsp;<img src="images\/check_icon.png" alt="" \/>';
					   }	
        
			    var proceed = true;	
       
        if(proceed){
        	
        	document.getElementById('loader').innerHTML = '&nbsp;<img src="images\/ajax-loader.gif" alt="" \/>';
        	
           //data to be sent to server         
            var m_data = new FormData();    
            m_data.append( 'user_name', $('input[name=name]').val());
            m_data.append( 'user_email', $('input[name=email]').val());
            //m_data.append( 'country_code', $('input[name=phone1]').val());
            //m_data.append( 'phone_number', $('input[name=phone2]').val());
            m_data.append( 'user_societe', $('input[name=societe]').val());
            m_data.append( 'subject', $('select[name=subject]').val());
						m_data.append( 'msg', $('textarea[name=message]').val());
						m_data.append( 'file_attach', $('input[name=file_attach]')[0].files[0]);
			 
            //instead of $.post() we are using $.ajax()
            //that's because $.ajax() has more options and flexibly.
  			$.ajax({
              url: 'contact_me.php',
              data: m_data,
              processData: false,
              contentType: false,
              type: 'POST', 
              dataType:'json',
              success: function(response){
                 //load json data from server and output message     
 				if(response.type == 'error'){ //load json data from server and output message     
					output = '<div class="error">'+response.text+'</div>';
				}else{
						document.getElementById('submit_btn').style = 'display:none !important;';
						document.getElementById('loader').style = 'display:none !important;';
						document.getElementById('name').style.border = '1px solid #CFD8E0';
				    output = '<div style=\"display: inline-block !important; margin-bottom:20px\"><div style=\"float:left; margin-right:25px;\"><img src="images/send_ok.png" alt="" /></div><div style=\"float:left;padding-top:10px\"><font style=\"color:#1A7917; line-height:13px;\">Votre email a &eacute;t&eacute; envoy&eacute; avec succ&egrave;s...<br/>Nous prendrons contact avec vous dans les meilleurs d&eacute;lais.</font></div></div>';
				}
				$("#contact_form #contact_results").hide().html(output).slideDown();
              }
            });
			

        }
    });
    
    //reset previously set border colors and hide all message on .keyup()
    $("#contact_form  input[required=true], #contact_form textarea[required=true]").keyup(function() { 
        $(this).css('border-color',''); 
        $("#result").slideUp();
    });
});