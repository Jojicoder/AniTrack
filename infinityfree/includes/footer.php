<footer>
  <p>&copy; <?= date('Y') ?> AniTrack | Anime &amp; Manga Tracker</p>
</footer>

<script>
document.querySelectorAll('.has-dropdown > .dropdown-trigger').forEach(function(trigger) {
  trigger.addEventListener('click', function(e) {
    var li = this.parentElement;
    var isOpen = li.classList.contains('open');
    document.querySelectorAll('.has-dropdown').forEach(function(el) {
      if (el !== li) {
        el.classList.remove('open');
        var elTrigger = el.children[0];
        if (elTrigger) elTrigger.setAttribute('aria-expanded', 'false');
      }
    });
    e.preventDefault();
    e.stopPropagation();
    li.classList.toggle('open', !isOpen);
    this.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
  });
});
document.addEventListener('click', function(e) {
  if (!e.target.closest('.has-dropdown')) {
    document.querySelectorAll('.has-dropdown').forEach(function(el) {
      el.classList.remove('open');
      var trigger = el.children[0];
      if (trigger) trigger.setAttribute('aria-expanded', 'false');
    });
  }
});
</script>
</body>
</html>
