  </main>

  <footer class="shell-footer">
    <div class="shell-wrap">
      <span>IKAN0// HABIT TRACKER PROTOCOL &copy; <?= date('Y') ?> &mdash; <?= APP_VERSION ?></span>
      <span class="mono dim">PHP <?= PHP_VERSION ?> &middot; MYSQL <?= $db_connected ? 'ONLINE' : 'OFFLINE' ?></span>
    </div>
  </footer>

  <script src="<?= BASE_URL ?>assets/js/pages.js"></script>
</body>
</html>
