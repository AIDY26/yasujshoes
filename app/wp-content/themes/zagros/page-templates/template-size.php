<?php
/**
 * Template Name: راهنمای سایز
 * Foot length to EU / US / UK, the sole on a ruler, how to measure, and the size chart.
 * The tool runs in src/js/modules/size.js.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="pagehead wrap">
    <nav class="crumbs" aria-label="مسیر"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><span aria-current="page">راهنمای سایز</span></nav>
    <h1 class="h1"><span class="en">FIND YOUR <span class="dim">SIZE</span></span></h1>
    <p class="lead">طول کف پای خود را وارد کنید تا سایز مناسب را ببینید.</p>
  </section>

  <section class="wrap">
    <div class="sizetool" data-size-tool>
      <div class="sizetool__form">
        <div class="sizetool__row">
          <p class="small">طول کف پا</p>
          <div class="seg" role="group" aria-label="واحد">
            <button type="button" data-unit="cm" aria-pressed="true" class="en">cm</button>
            <button type="button" data-unit="in" aria-pressed="false" class="en">in</button>
          </div>
        </div>
        <p class="sizetool__val"><b class="num" data-size-out>26.5</b><span class="en" data-size-unit>cm</span></p>
        <label class="sr-only" for="foot">طول پا به سانتی‌متر</label>
        <input id="foot" class="range" type="range" min="20" max="32" step="0.5" value="26.5" data-size-range />
        <div class="sizetool__row">
          <p>فرم پا</p>
          <div class="seg" role="group" aria-label="پهنای پا">
            <button type="button" data-width="regular" aria-pressed="true">معمولی</button>
            <button type="button" data-width="wide" aria-pressed="false">پهن</button>
          </div>
        </div>
        <dl class="sizetool__out">
          <div><dt>سایز اروپایی</dt><dd data-out="eu">۴۲</dd></div>
          <div><dt>آمریکایی مردانه</dt><dd data-out="usm">۹</dd></div>
          <div><dt>آمریکایی زنانه</dt><dd data-out="usw">۱۰٫۵</dd></div>
          <div><dt>سایز انگلیسی</dt><dd data-out="uk">۸٫۵</dd></div>
        </dl>
        <p class="small">اگر بین دو سایز هستید، برای کفش رانینگ نیم سایز بزرگ‌تر انتخاب کنید.</p>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn" data-size-cta><span data-label>مشاهده کفش‌های سایز ۴۲</span></a>
      </div>
      <div class="sizetool__viz" aria-hidden="true">
        <svg viewBox="0 0 600 300" class="sole">
          <rect x="20" y="40" width="560" height="250" rx="22" class="sole__bed"/>
          <g data-sole><path class="sole__shape" d="M70 150c0-48 34-78 92-80 60-2 96 12 150 6 70-8 112-38 168-32 54 6 84 46 84 104s-30 98-84 104c-56 6-98-24-168-32-54-6-90 8-150 6-58-2-92-32-92-76z"/><g class="sole__lines"><path d="M140 108c-14 26-14 58 0 86"/><path d="M190 100c-12 30-12 66 0 98"/><path d="M380 92c-18 36-18 78 0 116"/><path d="M430 88c-16 38-16 84 0 124"/><path d="M478 92c-14 36-14 78 0 116"/></g></g>
          <g data-ruler class="ruler"></g>
        </svg>
        <span class="sole__marker" data-size-marker><span class="en" data-size-marker-txt>26.5 cm</span></span>
      </div>
    </div>
  </section>

  <section class="section" aria-labelledby="how-title">
    <div class="wrap">
      <header class="shead">
        <h2 class="h2" id="how-title"><span class="en">HOW TO <span class="dim">MEASURE</span></span></h2>
        <p class="lead">به یک برگه A4، یک خودکار و یک خط‌کش نیاز دارید. بهتر است عصر اندازه بگیرید، چون پا در طول روز کمی بزرگ‌تر می‌شود.</p>
      </header>
      <ol class="steps">
        <li><span class="num">01</span><h3>برگه را کنار دیوار بگذارید</h3><p>برگه را روی زمین و چسبیده به دیوار بگذارید. پاشنه پا را به دیوار بچسبانید و صاف بایستید.</p></li>
        <li><span class="num">02</span><h3>انتهای انگشت را علامت بزنید</h3><p>از کسی بخواهید جلوی بلندترین انگشت پا روی برگه خط بکشد.</p></li>
        <li><span class="num">03</span><h3>فاصله را اندازه بگیرید</h3><p>فاصله لبه برگه تا خط را با خط‌کش اندازه بگیرید. هر دو پا را اندازه بگیرید و عدد بزرگ‌تر را ملاک قرار دهید.</p></li>
        <li><span class="num">04</span><h3>هنوز مطمئن نیستید؟</h3><p>عکس پایتان را کنار خط‌کش در دایرکت بفرستید تا سایز مناسب همان مدل را بگوییم.</p></li>
      </ol>
    </div>
  </section>

  <section class="section" aria-labelledby="chart-title">
    <div class="wrap">
      <header class="shead"><h2 class="h2" id="chart-title"><span class="en">SIZE <span class="dim">CHART</span></span></h2></header>
      <div class="tablewrap">
        <table class="table">
          <thead><tr><th>طول پا (سانتی‌متر)</th><th>اروپایی</th><th>آمریکایی مردانه</th><th>آمریکایی زنانه</th><th>انگلیسی</th></tr></thead>
          <tbody>
            <tr><td>۲۳٫۵</td><td>۳۸</td><td>۵</td><td>۶٫۵</td><td>۴٫۵</td></tr>
            <tr><td>۲۴٫۵</td><td>۳۹</td><td>۶</td><td>۷٫۵</td><td>۵٫۵</td></tr>
            <tr><td>۲۵</td><td>۴۰</td><td>۷</td><td>۸٫۵</td><td>۶٫۵</td></tr>
            <tr><td>۲۵٫۵</td><td>۴۱</td><td>۸</td><td>۹٫۵</td><td>۷٫۵</td></tr>
            <tr><td>۲۶٫۵</td><td>۴۲</td><td>۹</td><td>۱۰٫۵</td><td>۸٫۵</td></tr>
            <tr><td>۲۷</td><td>۴۳</td><td>۱۰</td><td>۱۱٫۵</td><td>۹٫۵</td></tr>
            <tr><td>۲۸</td><td>۴۴</td><td>۱۱</td><td>۱۲٫۵</td><td>۱۰٫۵</td></tr>
            <tr><td>۲۸٫۵</td><td>۴۵</td><td>۱۲</td><td>۱۳٫۵</td><td>۱۱٫۵</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
<?php
get_footer();
