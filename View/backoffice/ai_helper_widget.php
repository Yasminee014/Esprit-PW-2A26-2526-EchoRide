<!-- ══════════════ AI HELPER WIDGET (Admin) ══════════════ -->
<style>
/* ── Bouton flottant ─────────────────────────────────── */
#aiHelperBtn {
    position: fixed; bottom: 28px; right: 28px; z-index: 9999;
    width: 62px; height: 62px; border-radius: 50%; border: none;
    background: linear-gradient(135deg, #1976D2, #42A5F5);
    color: #fff; font-size: 1.5rem; cursor: pointer;
    box-shadow: 0 6px 28px rgba(25,118,210,0.55);
    transition: transform 0.3s, box-shadow 0.3s;
    display: flex; align-items: center; justify-content: center;
}
#aiHelperBtn:hover { transform: scale(1.12); box-shadow: 0 10px 36px rgba(25,118,210,0.75); }
#aiHelperBtn .ai-notif {
    position: absolute; top: -3px; right: -3px; width: 16px; height: 16px;
    background: #ffa500; border-radius: 50%; border: 2px solid #0A1628;
    animation: aiNotifPulse 2s infinite;
}
@keyframes aiNotifPulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.3)} }

/* ── Panel AI Helper ─────────────────────────────────── */
#aiHelperPanel {
    position: fixed; bottom: 102px; right: 28px; z-index: 9999;
    width: 820px; height: 560px;
    border-radius: 20px;
    background: #0d1b2a;
    border: 1px solid rgba(25,118,210,0.3);
    box-shadow: 0 24px 70px rgba(0,0,0,0.6);
    display: flex; flex-direction: column; overflow: hidden;
    transform: scale(0.88) translateY(24px); opacity: 0;
    transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s;
    pointer-events: none;
}
#aiHelperPanel.open { transform: scale(1) translateY(0); opacity: 1; pointer-events: all; }

/* ── Header ──────────────────────────────────────────── */
.aih-header {
    padding: 14px 20px; display: flex; align-items: center; gap: 12px;
    background: linear-gradient(135deg, rgba(25,118,210,0.25), rgba(66,165,245,0.1));
    border-bottom: 1px solid rgba(25,118,210,0.2); flex-shrink: 0;
}
.aih-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg,#1976D2,#42A5F5);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem; box-shadow: 0 0 14px rgba(25,118,210,0.5); flex-shrink: 0;
}
.aih-header h4 { margin: 0; color: #e0e0e0; font-size: 0.95rem; }
.aih-header p  { margin: 0; color: #888; font-size: 0.72rem; }
.aih-online { width: 8px; height: 8px; background: #00e676; border-radius: 50%; box-shadow: 0 0 6px #00e676; }
.aih-close  { background: none; border: none; color: #888; cursor: pointer; font-size: 1.1rem; padding: 4px; margin-left: auto; transition: color 0.2s; }
.aih-close:hover { color: #fff; }

/* ── Body : chat + users ─────────────────────────────── */
.aih-body { display: flex; flex: 1; overflow: hidden; }

/* Chat */
.aih-chat { flex: 1; display: flex; flex-direction: column; border-right: 1px solid rgba(255,255,255,0.07); }
.aih-messages { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; scrollbar-width: thin; }
.aih-msg { display: flex; gap: 8px; max-width: 90%; animation: aiFade 0.3s ease; }
@keyframes aiFade { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
.aih-msg.user { align-self: flex-end; flex-direction: row-reverse; }
.aih-msg.ai   { align-self: flex-start; }
.aih-msg-av { width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; }
.aih-msg.user .aih-msg-av { background: linear-gradient(135deg,#1976D2,#42A5F5); color: #fff; }
.aih-msg.ai   .aih-msg-av { background: linear-gradient(135deg,#1e3a5f,#0F3B6E); color: #61B3FA; border: 1px solid rgba(25,118,210,0.3); }
.aih-bubble { padding: 9px 13px; border-radius: 14px; font-size: 0.84rem; line-height: 1.5; word-break: break-word; }
.aih-msg.user .aih-bubble { background: linear-gradient(135deg,#1976D2,#42A5F5); color: #fff; border-bottom-right-radius: 4px; }
.aih-msg.ai   .aih-bubble { background: rgba(255,255,255,0.06); color: #e0e0e0; border: 1px solid rgba(255,255,255,0.1); border-bottom-left-radius: 4px; }
.aih-msg.ai   .aih-bubble code { background: rgba(25,118,210,0.2); color: #61B3FA; padding: 1px 5px; border-radius: 4px; }
.aih-msg.ai   .aih-bubble strong { color: #fff; }
.aih-time { font-size: 0.65rem; color: #555; margin-top: 3px; padding: 0 3px; align-self: flex-end; }

.aih-typing { display:flex; gap:5px; padding:10px 13px; }
.aih-typing span { width:6px; height:6px; border-radius:50%; background:#1976D2; animation:aidot 1.2s infinite; }
.aih-typing span:nth-child(2){animation-delay:.2s} .aih-typing span:nth-child(3){animation-delay:.4s}
@keyframes aidot { 0%,80%,100%{opacity:.2;transform:scale(.8)} 40%{opacity:1;transform:scale(1)} }

/* Chips */
.aih-chips { padding: 8px 14px 0; display: flex; flex-wrap: wrap; gap: 6px; flex-shrink: 0; }
.aih-chip { background: rgba(25,118,210,0.1); border: 1px solid rgba(25,118,210,0.3); color: #61B3FA; padding: 4px 10px; border-radius: 14px; font-size: 0.72rem; cursor: pointer; transition: background 0.2s; white-space: nowrap; }
.aih-chip:hover { background: rgba(25,118,210,0.25); }

/* Voice bar */
.aih-voice-bar { display:none; align-items:center; gap:8px; padding:6px 14px; background:rgba(255,82,82,0.1); border-top:1px solid rgba(255,82,82,0.2); color:#ff5252; font-size:0.78rem; flex-shrink:0; }
.aih-voice-bar.show { display:flex; }
.aih-voice-bar.speaking { background:rgba(25,118,210,0.08); border-color:rgba(25,118,210,0.2); color:#61B3FA; }
.aih-vbars { display:flex; gap:3px; align-items:center; }
.aih-vbars span { width:3px; background:currentColor; border-radius:2px; animation:aivbar .8s ease-in-out infinite; }
.aih-vbars span:nth-child(1){height:6px;animation-delay:0s} .aih-vbars span:nth-child(2){height:12px;animation-delay:.15s}
.aih-vbars span:nth-child(3){height:18px;animation-delay:.3s} .aih-vbars span:nth-child(4){height:12px;animation-delay:.45s}
.aih-vbars span:nth-child(5){height:6px;animation-delay:.6s}
@keyframes aivbar { 0%,100%{transform:scaleY(.4)} 50%{transform:scaleY(1)} }

/* Input */
.aih-input-bar { padding: 10px 12px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; gap: 8px; align-items: flex-end; background: rgba(0,0,0,0.2); flex-shrink: 0; }
#aihMicBtn { width:38px; height:38px; border-radius:10px; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.06); color:#aaa; cursor:pointer; font-size:.9rem; flex-shrink:0; transition:all .2s; }
#aihMicBtn:hover  { background:rgba(255,100,100,.2); color:#ff6b6b; }
#aihMicBtn.listen { background:rgba(255,82,82,.25); color:#ff5252; border-color:#ff5252; animation:aihPulseMic 1s infinite; }
#aihMicBtn.speak  { background:rgba(25,118,210,.15); color:#61B3FA; border-color:#1976D2; }
@keyframes aihPulseMic { 0%,100%{box-shadow:0 0 0 0 rgba(255,82,82,.4)} 50%{box-shadow:0 0 0 7px rgba(255,82,82,0)} }
#aihInput { flex:1; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:10px; padding:9px 13px; color:#e0e0e0; font-size:.84rem; resize:none; outline:none; max-height:90px; min-height:38px; font-family:inherit; line-height:1.4; }
#aihInput:focus { border-color:rgba(25,118,210,.5); }
#aihInput::placeholder { color:#444; }
#aihSend { width:38px; height:38px; border-radius:10px; border:none; background:linear-gradient(135deg,#1976D2,#42A5F5); color:#fff; cursor:pointer; font-size:.9rem; flex-shrink:0; transition:transform .2s; }
#aihSend:hover:not(:disabled) { transform:translateY(-2px); }
#aihSend:disabled { opacity:.4; cursor:not-allowed; }

/* Users panel */
.aih-users { width: 270px; display: flex; flex-direction: column; flex-shrink: 0; }
.aih-up-header { padding: 13px 16px; border-bottom: 1px solid rgba(255,255,255,0.07); background: rgba(25,118,210,0.05); flex-shrink: 0; }
.aih-up-header h5 { margin: 0; color: #e0e0e0; font-size: 0.88rem; }
.aih-up-header p  { margin: 3px 0 0; color: #666; font-size: 0.7rem; }
.aih-users-list { flex: 1; overflow-y: auto; padding: 8px; display: flex; flex-direction: column; gap: 7px; scrollbar-width: thin; }
.aih-ucard { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 9px; padding: 8px 11px; transition: border .2s; }
.aih-ucard:hover { border-color: rgba(25,118,210,0.4); }
.aih-ucard .ucn { color: #e0e0e0; font-size: 0.82rem; font-weight: 600; }
.aih-ucard .uce { color: #888; font-size: 0.7rem; margin-top: 2px; }
.aih-ucard .uci { color: #555; font-size: 0.67rem; margin-top: 1px; }
.aih-badge-actif   { background:rgba(0,230,118,.15); color:#00e676; padding:1px 7px; border-radius:9px; font-size:.63rem; float:right; }
.aih-badge-inactif { background:rgba(255,82,82,.15);  color:#ff5252; padding:1px 7px; border-radius:9px; font-size:.63rem; float:right; }
.aih-empty { text-align:center; color:#555; padding:30px 16px; font-size:.8rem; }
</style>

<!-- Floating Button -->
<button id="aiHelperBtn" onclick="toggleAIHelper()" title="AI Helper 🤖">
    <i class="fas fa-robot" id="aihBtnIcon"></i>
    <span class="ai-notif" id="aihNotif"></span>
</button>

<!-- AI Helper Panel -->
<div id="aiHelperPanel">
    <div class="aih-header">
        <div class="aih-avatar"><i class="fas fa-robot"></i></div>
        <div>
            <h4>AI Helper</h4>
            <p>Gestion utilisateurs • Voix FR / EN / Arabe</p>
        </div>
        <div class="aih-online"></div>
        <button class="aih-close" onclick="toggleAIHelper()"><i class="fas fa-times"></i></button>
    </div>

    <div class="aih-body">
        <!-- Chat -->
        <div class="aih-chat">
            <div class="aih-messages" id="aihMessages">
                <div class="aih-msg ai">
                    <div class="aih-msg-av"><i class="fas fa-robot"></i></div>
                    <div>
                        <div class="aih-bubble">
                            Bonjour ! Je suis <strong>AI Helper</strong> 🤖<br>
                            Je peux <strong>lister, ajouter, modifier, supprimer et bloquer</strong> des utilisateurs.<br>
                            Parlez-moi ou tapez votre commande 🎤
                        </div>
                        <div class="aih-time">Maintenant</div>
                    </div>
                </div>
            </div>

            <div class="aih-chips" id="aihChips">
                <span class="aih-chip" onclick="aihUseSugg(this)">📋 Liste tous</span>
                <span class="aih-chip" onclick="aihUseSugg(this)">🔍 Cherche un utilisateur</span>
                <span class="aih-chip" onclick="aihUseSugg(this)">🔃 Trie par nom</span>
                <span class="aih-chip" onclick="aihUseSugg(this)">➕ Ajouter un utilisateur</span>
                <span class="aih-chip" onclick="aihUseSugg(this)">🚫 Bloquer un utilisateur</span>
                <span class="aih-chip" onclick="aihUseSugg(this)">💬 Aide</span>
            </div>

            <div class="aih-voice-bar" id="aihVoiceBar">
                <div class="aih-vbars"><span></span><span></span><span></span><span></span><span></span></div>
                <span id="aihVoiceText">Écoute...</span>
                <span onclick="aihStopVoice()" style="margin-left:auto;cursor:pointer;font-size:.75rem;text-decoration:underline;">Arrêter</span>
            </div>

            <div class="aih-input-bar">
                <button id="aihMicBtn" onclick="aihToggleMic()" title="Parler"><i class="fas fa-microphone"></i></button>
                <textarea id="aihInput" placeholder="Tapez ou parlez... 🎤" rows="1"></textarea>
                <button id="aihSend" onclick="aihSendMsg()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>

        <!-- Users list -->
        <div class="aih-users">
            <div class="aih-up-header">
                <h5><i class="fas fa-users" style="color:#1976D2;margin-right:6px;"></i>Utilisateurs <span id="aihUCount" style="color:#61B3FA;"></span></h5>
                <p>Mis à jour en temps réel</p>
            </div>
            <div class="aih-users-list" id="aihUsersList">
                <div class="aih-empty"><i class="fas fa-spinner fa-spin"></i><br>Chargement...</div>
            </div>
        </div>
    </div>
</div>

<script>
var aihOpen = false;

function toggleAIHelper() {
    aihOpen = !aihOpen;
    document.getElementById('aiHelperPanel').classList.toggle('open', aihOpen);
    document.getElementById('aihNotif').style.display = aihOpen ? 'none' : '';
    document.getElementById('aihBtnIcon').className = aihOpen ? 'fas fa-times' : 'fas fa-robot';
    if (aihOpen) { document.getElementById('aihInput').focus(); aihLoadUsers(); }
}

function aihNow() { return new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}); }

function aihUseSugg(el) {
    var txt = el.textContent.replace(/^[^\s]+\s/, '').trim();
    document.getElementById('aihInput').value = txt;
    document.getElementById('aihInput').dispatchEvent(new Event('input'));
    aihSendMsg();
}

function aihAddMsg(role, text) {
    var html = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>')
        .replace(/`([^`]+)`/g,'<code>$1</code>')
        .replace(/\n/g,'<br>');
    var d = document.createElement('div');
    d.className = 'aih-msg ' + role;
    var icon = role==='user'?'fas fa-user':'fas fa-robot';
    d.innerHTML = '<div class="aih-msg-av"><i class="'+icon+'"></i></div>' +
        '<div><div class="aih-bubble">'+html+'</div><div class="aih-time">'+aihNow()+'</div></div>';
    var msgs = document.getElementById('aihMessages');
    msgs.appendChild(d); msgs.scrollTop = msgs.scrollHeight;
}

function aihShowTyping() {
    var d = document.createElement('div'); d.id='aihTyping'; d.className='aih-msg ai';
    d.innerHTML='<div class="aih-msg-av"><i class="fas fa-robot"></i></div>'+
        '<div class="aih-bubble"><div class="aih-typing"><span></span><span></span><span></span></div></div>';
    var msgs=document.getElementById('aihMessages'); msgs.appendChild(d); msgs.scrollTop=msgs.scrollHeight;
}
function aihHideTyping() { var t=document.getElementById('aihTyping'); if(t) t.remove(); }

function aihRenderUsers(list) {
    var cnt = document.getElementById('aihUCount');
    var ul  = document.getElementById('aihUsersList');
    cnt.textContent = '('+list.length+')';
    if (!list.length) { ul.innerHTML='<div class="aih-empty"><i class="fas fa-users-slash"></i><br>Aucun utilisateur</div>'; return; }
    ul.innerHTML = list.map(function(u) {
        var badge = u.statut==='actif'
            ? '<span class="aih-badge-actif">actif</span>'
            : '<span class="aih-badge-inactif">inactif</span>';
        return '<div class="aih-ucard">'+
            '<div><span style="color:#555;font-size:.65rem;">#'+u.id+'</span>'+badge+'</div>'+
            '<div class="ucn">'+u.prenom+' '+u.nom+'</div>'+
            '<div class="uce"><i class="fas fa-envelope" style="width:12px;"></i> '+u.email+'</div>'+
            (u.telephone?'<div class="uci"><i class="fas fa-phone" style="width:12px;"></i> '+u.telephone+'</div>':'')+
            '<div class="uci" style="color:#1976D2;"><i class="fas fa-tag" style="width:12px;"></i> '+(u.role||'passager')+'</div>'+
            '</div>';
    }).join('');
}

function aihRenderTable(list) {
    if (!list.length) { aihAddMsg('ai','Aucun utilisateur trouvé.'); return; }
    var rows = list.map(function(u) {
        var badge = u.statut==='actif'
            ? '<span style="background:rgba(0,230,118,.15);color:#00e676;padding:2px 7px;border-radius:8px;font-size:.68rem;">actif</span>'
            : '<span style="background:rgba(255,82,82,.15);color:#ff5252;padding:2px 7px;border-radius:8px;font-size:.68rem;">inactif</span>';
        return '<tr>'+
            '<td style="padding:4px 7px;color:#aaa;">#'+u.id+'</td>'+
            '<td style="padding:4px 7px;color:#e0e0e0;font-weight:600;">'+u.prenom+' '+u.nom+'</td>'+
            '<td style="padding:4px 7px;color:#888;font-size:.75rem;">'+u.email+'</td>'+
            '<td style="padding:4px 7px;color:#1976D2;font-size:.75rem;">'+(u.role||'-')+'</td>'+
            '<td style="padding:4px 7px;">'+badge+'</td>'+
            '</tr>';
    }).join('');
    var tableHtml = '<div style="overflow-x:auto;margin-top:5px;">'+
        '<table style="width:100%;border-collapse:collapse;font-size:.76rem;">'+
        '<thead><tr style="border-bottom:1px solid rgba(255,255,255,.1);">'+
        '<th style="padding:4px 7px;color:#61B3FA;text-align:left;">ID</th>'+
        '<th style="padding:4px 7px;color:#61B3FA;text-align:left;">Nom</th>'+
        '<th style="padding:4px 7px;color:#61B3FA;text-align:left;">Email</th>'+
        '<th style="padding:4px 7px;color:#61B3FA;text-align:left;">Rôle</th>'+
        '<th style="padding:4px 7px;color:#61B3FA;text-align:left;">Statut</th>'+
        '</tr></thead><tbody>'+rows+'</tbody></table></div>';
    var d = document.createElement('div'); d.className='aih-msg ai';
    d.innerHTML='<div class="aih-msg-av"><i class="fas fa-robot"></i></div>'+
        '<div><div class="aih-bubble" style="max-width:100%;padding:10px 13px;">'+tableHtml+'</div>'+
        '<div class="aih-time">'+aihNow()+'</div></div>';
    var msgs=document.getElementById('aihMessages'); msgs.appendChild(d); msgs.scrollTop=msgs.scrollHeight;
}

var aihSending = false;
async function aihSendMsg() {
    var msg = document.getElementById('aihInput').value.trim();
    if (!msg || aihSending) return;
    aihAddMsg('user', msg);
    document.getElementById('aihInput').value = '';
    document.getElementById('aihInput').style.height = 'auto';
    document.getElementById('aihSend').disabled = true;
    document.getElementById('aihChips').style.display = 'none';
    aihSending = true; aihShowTyping();
    try {
        var res  = await fetch('<?= BASE_URL ?>Controller/AIController.php?action=chat', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({message: msg})
        });
        var data = await res.json();
        aihHideTyping();
        var reply = data.reply || 'Pas de réponse.';
        aihAddMsg('ai', reply);
        aihSpeakText(reply);
        if (data.clients !== undefined) aihRenderUsers(data.clients);
        if (data.action==='list' && data.clients) aihRenderTable(data.clients);
    } catch(err) {
        aihHideTyping(); aihAddMsg('ai','Erreur réseau. Vérifiez votre connexion.');
    }
    document.getElementById('aihSend').disabled = false;
    aihSending = false;
}

async function aihLoadUsers() {
    try {
        var res  = await fetch('<?= BASE_URL ?>Controller/AIController.php?action=chat', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({message:'liste tous les utilisateurs'})
        });
        var data = await res.json();
        if (data.clients) aihRenderUsers(data.clients);
    } catch(e) {
        document.getElementById('aihUsersList').innerHTML='<div class="aih-empty">Erreur chargement</div>';
    }
}

document.getElementById('aihInput').addEventListener('keydown', function(e) {
    if (e.key==='Enter' && !e.shiftKey) { e.preventDefault(); aihSendMsg(); }
});
document.getElementById('aihInput').addEventListener('input', function() {
    this.style.height='auto'; this.style.height=Math.min(this.scrollHeight,90)+'px';
});

// ═══════════ VOICE INPUT ═══════════
var AihSpeechRec = window.SpeechRecognition||window.webkitSpeechRecognition;
var aihRecog=null, aihListening=false;
if (!AihSpeechRec) { var mb=document.getElementById('aihMicBtn'); mb.disabled=true; mb.style.opacity='.3'; }

function aihToggleMic() { if(!AihSpeechRec) return; aihListening?aihStopVoice():aihStartVoice(); }

function aihStartVoice() {
    if(window.speechSynthesis) window.speechSynthesis.cancel();
    aihRecog = new AihSpeechRec();
    aihRecog.lang='fr-FR'; aihRecog.continuous=false; aihRecog.interimResults=true;
    aihRecog.onstart = function() {
        aihListening=true;
        var mb=document.getElementById('aihMicBtn'); mb.className='listen'; mb.innerHTML='<i class="fas fa-stop"></i>';
        var vb=document.getElementById('aihVoiceBar'); vb.className='aih-voice-bar show';
        document.getElementById('aihVoiceText').textContent='Écoute... parlez !';
    };
    aihRecog.onresult = function(e) {
        var txt='';
        for(var i=e.resultIndex;i<e.results.length;i++) txt+=e.results[i][0].transcript;
        var inp=document.getElementById('aihInput'); inp.value=txt; inp.dispatchEvent(new Event('input'));
        document.getElementById('aihVoiceText').textContent=txt;
        if(e.results[e.results.length-1].isFinal) setTimeout(function(){aihSendMsg();},400);
    };
    aihRecog.onerror = function(e) {
        var m={'no-speech':'Aucune voix.','not-allowed':'Micro bloqué.','network':'Erreur réseau.'};
        document.getElementById('aihVoiceText').textContent=m[e.error]||'Erreur'; aihStopVoice();
    };
    aihRecog.onend = function() { if(aihListening) aihStopVoice(); };
    try { aihRecog.start(); } catch(e) {}
}

function aihStopVoice() {
    aihListening=false;
    var mb=document.getElementById('aihMicBtn'); mb.className=''; mb.innerHTML='<i class="fas fa-microphone"></i>';
    document.getElementById('aihVoiceBar').className='aih-voice-bar';
    if(aihRecog){try{aihRecog.stop();}catch(e){}}
}

// ═══════════ VOICE OUTPUT ═══════════
function aihSpeakText(text) {
    if(!window.speechSynthesis) return;
    var clean=text.replace(/[*_`#]/g,'').replace(/\n/g,' ').trim();
    if(!clean) return;
    window.speechSynthesis.cancel();
    var utt=new SpeechSynthesisUtterance(clean);
    utt.lang='fr-FR'; utt.rate=1.05; utt.pitch=1;
    var voices=window.speechSynthesis.getVoices();
    var frV=voices.find(function(v){return v.lang.startsWith('fr');});
    if(frV) utt.voice=frV;
    utt.onstart=function(){
        var mb=document.getElementById('aihMicBtn'); mb.className='speak'; mb.innerHTML='<i class="fas fa-volume-up"></i>';
        var vb=document.getElementById('aihVoiceBar'); vb.className='aih-voice-bar show speaking';
        document.getElementById('aihVoiceText').textContent='AI Helper parle...';
    };
    utt.onend=utt.onerror=function(){
        var mb=document.getElementById('aihMicBtn'); mb.className=''; mb.innerHTML='<i class="fas fa-microphone"></i>';
        document.getElementById('aihVoiceBar').className='aih-voice-bar';
    };
    window.speechSynthesis.speak(utt);
}
if(window.speechSynthesis) window.speechSynthesis.onvoiceschanged=function(){window.speechSynthesis.getVoices();};
</script>
