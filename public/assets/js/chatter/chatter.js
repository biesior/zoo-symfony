let lastUpdate = null,
    chatTimeout = 10000,
    chatInterval = null;

jQuery(document).ready(function () {
    updateChat()
    chatInterval = setInterval(updateChat, chatTimeout)

    function updateMoments() {
        jQuery('.moment-point').each(function () {
            let $point = jQuery(this)
            $point.html(moment($point.data('date')).fromNow())
        })
    }

    setInterval(updateMoments, 15000)
    setTimeout(fitChat, 200)
})

let $chatBoxInput = jQuery('#chat-box-input')

$chatBoxInput.on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        let msg = $chatBoxInput.val().trim();
        if (msg === '') return false;
        sendMessage(msg)
        $chatBoxInput.val('')
    }
});

function fitChat() {
    let jumboH = jQuery('.jumbotron').outerHeight(),
        chatBoxH = jQuery('#chat-box').outerHeight(),
        footerH = jQuery('.footer').height()
    let newHeightC = jumboH + chatBoxH + footerH + 80;
    let $chatMessages = jQuery('#chat-messages'),
        newHeight = jQuery(window).height() - newHeightC

    $chatMessages.height(newHeight)
}

jQuery(window).on('resize', fitChat)

function sendMessage(msg) {
    jQuery.ajax({
        url: '/chat/send',
        method: 'POST',
        data: {message: msg}
    }).done(function () {
        clearInterval(chatInterval)
        setTimeout(function () {
            updateChat()
            chatInterval = setInterval(updateChat, chatTimeout)
        }, 200)
    });
}

function updateChat() {
    console.log('upcha')
    let updateUrl = "/chat/fetch-all"
    if (lastUpdate !== null) {
        updateUrl = updateUrl + "?updated-at=" + lastUpdate
    }
    jQuery.ajax({
        url: updateUrl
    }).done(function (data) {
        let jsonData = $.parseJSON(data.data)
        let $chatMessages = $('#chat-messages')
        let newPostCount = 0
        jsonData.messages.forEach(function (message) {
            if (message.t === 'uj') {
                // console.log('skip this msg, it is member info')
            } else {
                let name = message.u.name
                if (typeof name === 'undefined') {
                    name = message.u.username
                }
                lastUpdate = message._updatedAt
                newPostCount++
                // console.log(message)
                $chatMessages.append(formatMessage(name, message.msg, message._updatedAt, message.u.username))
            }
        })

        if (newPostCount > 0) {
            setTimeout(function () {
                $chatMessages.scrollTop($chatMessages[0].scrollHeight);
                fitChat()
            }, 200)
        }
    });
}


function formatMessage(author, body, date, username) {
    /* todo: change URL to dynamic*/
    return '' +
        '<div class="card bg-light card-full-width card-chat">' +
        '   <div class="card-body clearfix">' +
        '       <div class="float-left">' +
        '           <img src="http://localhost:3000/avatar/' + username + '" width="40" height="40">' +
        '       </div>' +
        '       <div class="float-left post-main-col">' +
        '           <span class="post-data">' +
        '               <b>' + author + '</b>' +
        '               <span class="text-dimmed moment-point" data-date="' + date + '" title="Posted at: ' + date + '">' + moment(date).fromNow() + '</span>' +
        '           </span>' +
        '           <p class="post-body">' + escapeHtml(body) + '</p>' +
        '       </div>' +
        '   </div>' +
        '</div>'
}


let entityMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
    '/': '&#x2F;',
    '`': '&#x60;',
    '=': '&#x3D;'
};

function escapeHtml(string) {
    return String(string).replace(/[&<>"'`=\/]/g, function (s) {
        return entityMap[s];
    });
}