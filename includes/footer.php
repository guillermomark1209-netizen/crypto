<footer class="site-footer">
    <span>© CipherLab</span><span>Classical ciphers · modern curiosity</span>
</footer>
<script src="<?= isset($_SESSION['user']) && str_contains($_SERVER['SCRIPT_NAME'], '/ciphers/') ? '../assets/js/app.js' : 'assets/js/app.js' ?>" defer></script>
</body>
</html>
