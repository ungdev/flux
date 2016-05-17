var currentPanel = '';
var currentBtnName = '';
var targetId = 0;

// Started every 3 seconds
var refresh = function(again){
	if(again === undefined) {
		again = true;
	}

	var jqxhr = jQuery.getJSON( '/admin/json?panel='+currentPanel+'&id='+targetId, function(data) {
		if(!data) {
			$('.connexionState').html('<span class="glyphicon glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Impossible de mettre à jour !')
			$('.connexionState').css('color', 'red');
			return;
		}

		// Update channel list on the right
		if(!data.droitChannelList && !data.espaceChannelList) {
			$('#channels').html('Erreur de chargement..');
		}
		else {
			html = '<div class="list-group">';
			for (var chan in data.espaceChannelList) {
				if (data.espaceChannelList.hasOwnProperty(chan)) {
					var val = data.espaceChannelList[chan];
					var date = (new Date(val.derniere_connexion * 1000)).toLocaleTimeString();

					html += '<a href="#chat-user-'+val.login+'" class="list-group-item" data-btnname="chat-user-'+val.login+'" data-targetid="'+val.id+'" data-panel="chat-user" data-lastId="'+val.messageId+'" data-lastAuthorId="'+val.messageAuthorId+'" title="Dernière connexion : '+ date +'">';

					var since = (data.timestamp - val.derniere_connexion);
					if (since != null && since < 30) {
						html += '<span class="logged-in">&bull;</span>'
					}
					else {
						html += '<span class="logged-out">&bull;</span>'
					}
					html += val.login+'</a>';
				}
			}

			html += '</div><div class="list-group">';
			for (var chan in data.droitChannelList) {
				if (data.droitChannelList.hasOwnProperty(chan)) {
					var val = data.droitChannelList[chan];

					html += '<a href="#chat-group-'+val.nom+'" class="list-group-item" data-btnname="chat-group-'+val.nom+'" data-targetid="'+val.id+'" data-panel="chat-group" data-lastid="'+val.messageId+'" data-lastauthorid="'+val.messageAuthorId+'">'+val.nom+'</a>';
				}
			}
			html += '</div>';
			$('#channels').html(html);
		}


		// On tab click
		$('#panel-menu .list-group-item').off('click', null);
		$('#panel-menu .list-group-item').on('click', null, function() {
			tabClick($(this));
		});

		// Active the button
		$('#panel-menu .list-group-item').each(function() {
			if($(this).data('btnname') == currentBtnName) {
				$(this).addClass('active');
			}
			else {
				$(this).removeClass('active');
			}
		})

		// EAT informations
		if(data.espace) {
			$('#flux-title').html('Flux ' + data.espace.nom + ' (' + data.espace.lieu + ')');
		}
		// Panel stuff
		if(data.problemList && data.espace) {
			if($('#problems').data('array') === undefined || $('#problems').data('array') != JSON.stringify(data.problemList))
			{
				var currentCat = -1;
				var html = '';
				for (var cat in data.problemList) {
					if (data.problemList.hasOwnProperty(cat)) {
						var val = data.problemList[cat];

						// Print category name once
						if(currentCat != val.id_cat_prob) {
							currentCat = val.id_cat_prob;
							html += '<h4>'+val.cat+'</h4>';
						}

						// Print buttons
						if(val.gravite == 2) {
							html += '<div class="btn-group btn-group-problem" role="group">'
								+ '<a href="/admin/problem?type='+ val.id_type_prob +'&espace='+data.espace.id+'&gravite=0&btn='+currentBtnName+'" class="btn btn-success linkToAjax" title="Annuler le signalement"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></a>'
								+ '<span class="btn btn-danger">'+val.nom+'</span>'
								+ '</div>';
						}
						else if(val.gravite == 1) {
							html += '<div class="btn-group btn-group-problem" role="group">'
								+ '<a href="/admin/problem?type='+ val.id_type_prob +'&espace='+data.espace.id+'&gravite=0&btn='+currentBtnName+'" class="btn btn-success linkToAjax" title="Annuler le signalement"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></a>'
								+ '<a href="/admin/problem?type='+ val.id_type_prob +'&espace='+data.espace.id+'&gravite=2&btn='+currentBtnName+'" class="btn btn-warning linkToAjax" title="Signaler que le problème est URGENT">'+val.nom+'</a>'
								+ '</div>';
						}
						else {
							html += '<a href="/admin/problem?type='+ val.id_type_prob +'&espace='+data.espace.id+'&gravite=1&btn='+currentBtnName+'" class="btn btn-default btn-problem linkToAjax" title="Signaler un problème">'+val.nom+'</a>';
						}
					}
				}
				$('#problems').html(html);
				$('#problems').data('array', JSON.stringify(data.problemList))
			}
		}
		if(data.fluxList && data.espace) {
			if($('#flux').data('array') === undefined || $('#flux').data('array') != JSON.stringify(data.fluxList))
			{
				currentCat = -1;
				html = '';
				for (var cat in data.fluxList) {
					if (data.fluxList.hasOwnProperty(cat)) {
						var val = data.fluxList[cat];

						// Print category name once
						if(currentCat != val.type_id) {
							if(currentCat != -1) {
								html += '</div>';
							}
							html += '<h4>'+val.type_name+' ('+val.conditionnement+')</h4>';
							currentCat = val.type_id;
						}

						// Print buttons
						if(val.fin != null) {
							html += '<div class="btn-group btn-group-problem" role="group">'
								+ '<a href="/admin/flux?level=1&stock='+val.id+'&espace='+data.espace.id+'&gravite=0&btn='+currentBtnName+'" class="btn btn-success linkToAjax" title="Annuler"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>'
								+ '<span class="btn btn-danger">'+val.identifiant+' Terminé</span>'
								+ '</div>';
						}
						else if(val.entame != null) {
							html += '<div class="btn-group btn-group-problem" role="group">'
								+ '<a href="/admin/flux?level=0&stock='+val.id+'&espace='+data.espace.id+'&gravite=0&btn='+currentBtnName+'" class="btn btn-success linkToAjax" title="Annuler"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>'
								+ '<a href="/admin/flux?level=2&stock='+val.id+'&espace='+data.espace.id+'&gravite=0&btn='+currentBtnName+'" class="btn btn-warning linkToAjax" title="Indiquer comment terminé">'+val.identifiant+' Entamé</a>'
								+ '</div>';
						}
						else {
							// html += '<div class="btn-group btn-group-problem" role="group">'
							// 	+ '<a href="#" class="btn btn-warning" title="Déplacer l\'élément"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></a>'
							// 	+ '<a href="/admin/flux?level=1&stock='+val.id+'&espace='+data.espace.id+'&gravite=0&btn='+currentBtnName+'" class="btn btn-default linkToAjax" title="Indiquer comment terminé">'+val.identifiant+' Entamé</a>'
							// 	+ '</div>';
							html += '<a href="/admin/flux?level=1&stock='+val.id+'&espace='+data.espace.id+'&gravite=0&btn='+currentBtnName+'" class="btn btn-default btn-problem linkToAjax" titleIndiquer comme entamé">'+val.identifiant+'</a>';

						}
					}
				}
				$('#flux').html(html);
				$('#flux').data('array', JSON.stringify(data.fluxList))
			}
		}
		if(data.messageList) {
			var lastId = 0;
			if(data.messageList.length > 0) {
				var lastId = data.messageList[data.messageList.length-1].id;
			}
			if($('.chat').data('lastId') === undefined || $('.chat').data('lastId') != lastId)
			{
				currentCat = -1;
				html = '';
				for (var msg in data.messageList) {
					if (data.messageList.hasOwnProperty(msg)) {
						var val = data.messageList[msg];

						var date = (new Date(val.date * 1000)).toLocaleTimeString();
						var color = 'black';
						var bold = 'bold';
						var author = val.login;
						if(val.me == 1) {
							bold = 'nomal';
						}
						if(val.droit) {
							author += ' → ' + val.droit
							color = 'red';
						}

						html += '<li class="clearfix"><div class="chat-body clearfix">'
							+ '<span class="time">['+ date +']</span> <span style="color:'+ color +';font-weight:'+bold+'" >'+ author  + '</span>: <br/>'
							+ val.message + '</div></li>';
					}
				}
				html += '</div>';
				$('.chat').html(html);
				$('.chat').data('lastId', lastId)
				$('.chat').each(function() {
					var parent = $(this).parent();
					var chat = $(this);
					parent.scrollTop( chat.height() - parent.height() );
				})
			}
		}

		// Restore from a #link
		if(!currentBtnName && window.location.hash && window.location.hash.length > 0) {
			$('#panel-menu .list-group-item').each(function() {
				if(window.location.hash.substring(1) == $(this).data('btnname')) {
					tabClick($(this));
				}
			})
		}

		// Recreate the link to ajax Event for every new items
		$('.linkToAjax').off('click', null, linkToAjax);
		$('.linkToAjax').on('click', null, linkToAjax);


		$('.connexionState').html('Mise à jour : ' + (new Date()).toLocaleTimeString())
		$('.connexionState').css('color', '#444');
		$('.chat-panel').find('input').prop('disabled', false);

	})
	.fail(function() {
		$('.connexionState').html('<span class="glyphicon glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Impossible de mettre à jour !')
		$('.connexionState').css('color', 'red');
	})
	.always(function() {
		if(again) {
			setTimeout(refresh, 3000);
		}
	});
};

// Chat notification blinking
var notificationState = false;
var originalTitle = document.title;
setInterval(function () {
	$('#channels .list-group-item').each(function() {
		if($(this).data('lastid') === undefined) {
			return;
		}

		// Blink on new message
		if(notificationState
			&& localStorage
			&& currentBtnName != $(this).data('btnname')
			&& localStorage.getItem('adminChatLastId-'+$(this).data('btnname')) != undefined
			&& localStorage.getItem('adminChatLastId-'+$(this).data('btnname')) != ($(this).data('lastid')+'')) {
				$(this).addClass('list-group-item-danger');
				document.title = 'Nouveau message !';
		}
		else if(localStorage) {
			if(notificationState) {
				localStorage.setItem('adminChatLastId-'+$(this).data('btnname'), ($(this).data('lastid')+''));
			}
			$(this).removeClass('list-group-item-danger');
			 document.title = originalTitle;
		}

	})
	notificationState = !notificationState;

}, 500);

// Started when user click on a button
var tabClick = function(button) {
	$('#panel-container').children().each(function(){
		$(this).css('display', 'none');
	})

	if(!button) {
		return;
	}

	// Show the panel
	if(button.data('panel') !== undefined && button.data('btnname') !== undefined) {
		$('#' + button.data('panel')).css('display', 'block');
		currentPanel = button.data('panel');
		currentBtnName = button.data('btnname');
		targetId = button.data('targetid');
	}

	// Disable notification on click
	if(button.data('lastid') !== undefined) {
		localStorage.setItem('adminChatLastId-'+button.data('btnname'), (button.data('lastid')+''));
	}

	// Force chat Refresh
	$('.chat').data('lastId', '-1');

	// Refresh everything
	refresh(false);
}

// Chat send message
var input = $('.chat-panel').find('input');
function sendMessage(input) {
	var val = input.val();
	if(val.length >= 1) {
		$.post('/admin/send', {'message' : val, 'target' : targetId, 'panel': currentPanel}, function(){ refresh(false); })
		input.focus();
		input.prop('disabled', true);
		input.val('');
	}
}
// Event that send message
$('.chat-panel').find('button').click(function() {
	sendMessage($(this).parent().parent().find('input'));
});
input.keypress(function (e) {
	if (e.which == 13) {
		sendMessage($(this))
		return false;
	}
});

// Ajax instead of link function
function linkToAjax() {
	$.get($(this).attr('href'), [], function(){ refresh(false); })
	return false;
}

// Init
$(function() {
	tabClick();
	refresh();
})
