// js/player.js — PixelMusic YouTube Player

window.PixelPlayer = (() => {
    let ytPlayer = null;
    let queue = [];   // [{videoId, title, thumbnail, channelName}]
    let currentIdx = -1;
    let isReady = false;
    let isPlaying = false;

    // ── Load YouTube IFrame API ────────────────────────────────────────────────
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(tag);

    window.onYouTubeIframeAPIReady = () => {
        ytPlayer = new YT.Player('yt-iframe', {
            height: '1',
            width: '1',
            playerVars: {
                autoplay: 0,
                controls: 0,
                disablekb: 1,
                fs: 0,
                iv_load_policy: 3,
                modestbranding: 1,
                rel: 0,
            },
            events: {
                onReady: onPlayerReady,
                onStateChange: onPlayerStateChange,
            },
        });
    };

    function onPlayerReady() {
        isReady = true;
        updateVolumeUI();
    }

    function onPlayerStateChange(e) {
        if (e.data === YT.PlayerState.PLAYING) {
            isPlaying = true;
            updatePlayBtn(true);
            startProgressLoop();
            startTurntableSpin(true);
        } else if (e.data === YT.PlayerState.PAUSED) {
            isPlaying = false;
            updatePlayBtn(false);
            startTurntableSpin(false);
        } else if (e.data === YT.PlayerState.ENDED) {
            playNext();
        } else if (e.data === YT.PlayerState.BUFFERING) {
            updatePlayBtn(true);
        }
    }

    // ── Playback controls ──────────────────────────────────────────────────────
    function playSong(song, queueList) {
        if (queueList) {
            queue = queueList;
            currentIdx = queue.findIndex(s => s.videoId === song.videoId);
            if (currentIdx === -1) { queue.unshift(song); currentIdx = 0; }
        } else if (currentIdx === -1) {
            queue = [song]; currentIdx = 0;
        }
        if (!isReady) { setTimeout(() => playSong(song, null), 300); return; }
        ytPlayer.loadVideoById(song.videoId);
        updateNowPlaying(song);
        saveToRecent(song);
    }

    function togglePlayPause() {
        if (!ytPlayer || !isReady) return;
        if (isPlaying) ytPlayer.pauseVideo();
        else ytPlayer.playVideo();
    }

    function playNext() {
        if (!queue.length) return;
        currentIdx = (currentIdx + 1) % queue.length;
        playSong(queue[currentIdx], null);
    }

    function playPrev() {
        if (!queue.length) return;
        currentIdx = (currentIdx - 1 + queue.length) % queue.length;
        playSong(queue[currentIdx], null);
    }

    function setVolume(v) {
        if (ytPlayer && isReady) ytPlayer.setVolume(v);
    }

    // ── UI updates ─────────────────────────────────────────────────────────────
    function updateNowPlaying(song) {
        const titleEl = document.getElementById('np-title');
        const chanEl = document.getElementById('np-channel');
        const thumbEl = document.getElementById('np-thumb');
        const tickEl = document.getElementById('ticker-np');

        if (titleEl) titleEl.textContent = song.title;
        if (chanEl) chanEl.textContent = song.channelName;
        if (thumbEl) { thumbEl.src = song.thumbnail; thumbEl.style.display = 'block'; }
        if (tickEl) tickEl.textContent = `🎵 Now Playing: ${song.title} // `;

        // Show player bar
        const bar = document.getElementById('player-bar');
        if (bar) bar.classList.add('visible');

        // Highlight active song row
        document.querySelectorAll('.song-row').forEach(r => r.classList.remove('playing'));
        const row = document.querySelector(`.song-row[data-video="${song.videoId}"]`);
        if (row) row.classList.add('playing');
    }

    function updatePlayBtn(playing) {
        document.querySelectorAll('.ctrl-play').forEach(btn => {
            btn.textContent = playing ? '⏸' : '▶';
        });
    }

    function startTurntableSpin(spin) {
        document.querySelectorAll('.turntable-anim').forEach(el => {
            el.style.animationPlayState = spin ? 'running' : 'paused';
        });
    }

    let progressTimer = null;
    function startProgressLoop() {
        clearInterval(progressTimer);
        progressTimer = setInterval(() => {
            if (!ytPlayer || !isReady || !isPlaying) return;
            const dur = ytPlayer.getDuration() || 0;
            const cur = ytPlayer.getCurrentTime() || 0;
            const pct = dur > 0 ? (cur / dur) * 100 : 0;
            const bar = document.getElementById('progress-fill');
            const cur2 = document.getElementById('time-current');
            const dur2 = document.getElementById('time-duration');
            if (bar) bar.style.width = pct + '%';
            if (cur2) cur2.textContent = formatTime(cur);
            if (dur2) dur2.textContent = formatTime(dur);
        }, 500);
    }

    function updateVolumeUI() {
        const vol = document.getElementById('volume-slider');
        if (vol) setVolume(parseInt(vol.value));
    }

    function formatTime(s) {
        s = Math.floor(s);
        const m = Math.floor(s / 60);
        const sec = String(s % 60).padStart(2, '0');
        return `${m}:${sec}`;
    }

    // ── Seek on progress bar click ─────────────────────────────────────────────
    function seekTo(pct) {
        if (!ytPlayer || !isReady) return;
        const dur = ytPlayer.getDuration() || 0;
        ytPlayer.seekTo(dur * pct, true);
    }

    // ── Recent songs (localStorage) ────────────────────────────────────────────
    function saveToRecent(song) {
        try {
            let recent = JSON.parse(localStorage.getItem('pm_recent') || '[]');
            recent = recent.filter(s => s.videoId !== song.videoId);
            recent.unshift(song);
            recent = recent.slice(0, 10);
            localStorage.setItem('pm_recent', JSON.stringify(recent));
        } catch (e) { }
    }

    function getRecent() {
        try { return JSON.parse(localStorage.getItem('pm_recent') || '[]'); }
        catch (e) { return []; }
    }

    // ── Search ─────────────────────────────────────────────────────────────────
    async function search(query) {
        const res = await fetch(`youtube_search.php?q=${encodeURIComponent(query)}&max=12`);
        const data = await res.json();
        return data.items || [];
    }

    return { playSong, togglePlayPause, playNext, playPrev, setVolume, seekTo, search, getRecent, queue: () => queue, currentIdx: () => currentIdx };
})();