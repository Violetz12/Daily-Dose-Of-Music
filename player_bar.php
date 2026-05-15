<?php // includes/player_bar.php ?>

<!-- Hidden YouTube iframe (audio only) -->
<div id="yt-iframe"></div>

<!-- Persistent bottom player bar -->
<div id="player-bar">
  <img id="np-thumb" class="pb-thumb" src="" alt="cover">

  <div class="pb-info">
    <div class="pb-title" id="np-title">Nothing playing</div>
    <div class="pb-channel" id="np-channel">—</div>
  </div>

  <div class="pb-controls">
    <button class="pb-btn" onclick="PixelPlayer.playPrev()" title="Previous">⏮</button>
    <button class="pb-btn ctrl-play" onclick="PixelPlayer.togglePlayPause()" title="Play/Pause">▶</button>
    <button class="pb-btn" onclick="PixelPlayer.playNext()" title="Next">⏭</button>
  </div>

  <div class="pb-progress">
    <div class="pb-track" id="progress-track" title="Click to seek">
      <div id="progress-fill"></div>
    </div>
    <div class="pb-times">
      <span id="time-current">0:00</span>
      <span id="time-duration">0:00</span>
    </div>
  </div>

  <div class="pb-volume">
    <span>🔊</span>
    <input type="range" id="volume-slider" min="0" max="100" value="80"
           oninput="PixelPlayer.setVolume(this.value)">
  </div>
</div>

<script src="js/player.js"></script>
<script>
// Seek on progress track click
document.getElementById('progress-track').addEventListener('click', function(e) {
  const rect = this.getBoundingClientRect();
  const pct  = (e.clientX - rect.left) / rect.width;
  PixelPlayer.seekTo(pct);
});
</script>