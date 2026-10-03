<?php
/**
 * Rodapé.
 *
 * @package the-classicpress-theme
 */
?>
<footer class="site-footer">

  <a href="<?php bloginfo( 'rss2_url' ); ?>">
    <img
      src="<?php echo esc_url( content_url( '/uploads/Feed-icon.svg' ) ); ?>"
      alt="RSS"
      width="30"
      height="30"
    />
  </a>

  <div class="footer-badges">
    <div class="badge-row">
      <a
        href="https://lerama.pcdomanual.com"
        target="_blank"
        rel="noopener noreferrer"
      >
        <img
          src="<?php echo esc_url( content_url( '/uploads/lerama176x62.gif' ) ); ?>"
          alt="Lerama"
          width="88"
          height="31"
        />
      </a>
      <a
        href="https://entreblogs.com.br"
        target="_blank"
        rel="noopener noreferrer"
      >
        <img
          src="<?php echo esc_url( content_url( '/uploads/Entreblogs_88x31.gif' ) ); ?>"
          alt="Entreblogs"
          width="88"
          height="31"
        />
      </a>
    </div>

    <div class="badge-row">
      <a
        href="https://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1"
        target="_blank"
        rel="license noopener noreferrer"
      >
        <img
          src="<?php echo esc_url( content_url( '/uploads/by_nc_sa.svg' ) ); ?>"
          alt="Licença Creative Commons BY-NC-SA 4.0"
          width="120"
          height="42"
        />
      </a>
      <a
        href="https://notbyai.fyi/hi/not-by-ai/"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Written by Human, Not by AI"
      >
        <img
          src="https://notbyai.fyi/img/written-by-human-not-by-ai-white.svg"
          alt="Written by Human, Not by AI"
          width="120"
          height="42"
        />
      </a>
    </div>
  </div>
</footer>
</div><!-- .site -->
<?php wp_footer(); ?>
</body>
</html>
