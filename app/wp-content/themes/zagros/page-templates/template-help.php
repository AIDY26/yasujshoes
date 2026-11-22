<?php
/**
 * Template Name: سوالات متداول
 * Shipping, swaps, originality and orders. Edit the answers here (or move them to page content).
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="pagehead pagehead--split wrap">
    <div>
      <nav class="crumbs" aria-label="مسیر"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><span aria-current="page">سوالات متداول</span></nav>
      <h1 class="h1"><span class="en">HOW CAN WE <span class="dim">HELP?</span></span></h1>
      <p class="lead">جواب سوالات پرتکرار درباره ارسال، تعویض و سفارش. اگر جواب سوالتان را پیدا نکردید، در دایرکت اینستاگرام بپرسید.</p>
    </div>
    <a class="talk" href="<?php echo esc_url( lc_home()['general']['instagram'] ); ?>" target="_blank" rel="noopener"><span><b>پشتیبانی</b><small>پاسخگویی در دایرکت اینستاگرام، هر روز ۱۰ صبح تا ۱۰ شب</small></span><span class="btn btn--sm"><span class="en"><?php echo esc_html( lc_instagram_handle() ); ?></span></span></a>
  </section>

  <nav class="wrap helptabs" aria-label="بخش‌ها">
    <div class="helptabs__in">
      <a href="#shipping" class="chip is-on" data-help-tab>ارسال سفارش</a>
      <a href="#swap" class="chip" data-help-tab>تعویض و مرجوعی</a>
      <a href="#original" class="chip" data-help-tab>اصالت کالا</a>
      <a href="#orders" class="chip" data-help-tab>سفارش و پرداخت</a>
    </div>
  </nav>

  <div class="wrap helpblocks">
    <section class="helpblock" id="shipping" data-help-block>
      <h2 class="h3"><span class="en">SHIPPING</span><span class="helpblock__fa">ارسال سفارش</span></h2>
      <div class="accs">
        <details class="acc" data-acc open><summary>هزینه ارسال چقدر است؟<span class="acc__icon" aria-hidden="true"></span></summary><div class="acc__body"><p>برای خریدهای بالای ۳ میلیون تومان ارسال رایگان است. برای سفارش‌های کمتر، هزینه پست پیشتاز ۹۰ هزار تومان است. ارسال با تیپاکس هم به‌صورت پس‌کرایه انجام می‌شود.</p></div></details>
        <details class="acc" data-acc><summary>سفارش چند روزه به دستم می‌رسد؟<span class="acc__icon" aria-hidden="true"></span></summary><div class="acc__body"><p>سفارش‌هایی که تا ساعت ۱۴ ثبت شوند، همان روز ارسال می‌شوند. زمان تحویل در تهران ۱ تا ۲ روز کاری و در شهرستان‌ها ۲ تا ۴ روز کاری است.</p></div></details>
        <details class="acc" data-acc><summary>به همه شهرها ارسال دارید؟<span class="acc__icon" aria-hidden="true"></span></summary><div class="acc__body"><p>بله، به سراسر کشور ارسال می‌کنیم.</p></div></details>
      </div>
    </section>
    <section class="helpblock" id="swap" data-help-block>
      <h2 class="h3"><span class="en">SWAPS</span><span class="helpblock__fa">تعویض و مرجوعی</span></h2>
      <div class="accs">
        <details class="acc" data-acc><summary>اگر سایز مناسب نبود چه کار کنم؟<span class="acc__icon" aria-hidden="true"></span></summary><div class="acc__body"><p>تا ۷ روز بعد از تحویل، اگر کفش پوشیده نشده و جعبه سالم باشد، سایز آن را عوض می‌کنیم. هزینه ارسال مجدد با ماست.</p></div></details>
        <details class="acc" data-acc><summary>امکان مرجوع کردن کالا هست؟<span class="acc__icon" aria-hidden="true"></span></summary><div class="acc__body"><p>بله، تا ۷ روز بعد از تحویل. مبلغ ظرف ۳ روز کاری به حساب شما برمی‌گردد. هزینه ارسال مرجوعی بر عهده خریدار است.</p></div></details>
      </div>
    </section>
    <section class="helpblock" id="original" data-help-block>
      <h2 class="h3"><span class="en">ORIGINAL</span><span class="helpblock__fa">اصالت کالا</span></h2>
      <div class="accs">
        <details class="acc" data-acc><summary>از کجا مطمئن شوم کالا اورجینال است؟<span class="acc__icon" aria-hidden="true"></span></summary><div class="acc__body"><p>همه محصولات با جعبه و برچسب اصلی برند ارسال می‌شوند. اگر سوالی داشتید، قبل از پوشیدن کفش، عکس برچسب داخل زبانه را برای ما بفرستید.</p></div></details>
      </div>
    </section>
    <section class="helpblock" id="orders" data-help-block>
      <h2 class="h3"><span class="en">ORDERS</span><span class="helpblock__fa">سفارش و پرداخت</span></h2>
      <div class="accs">
        <details class="acc" data-acc><summary>کد رهگیری سفارش را از کجا ببینم؟<span class="acc__icon" aria-hidden="true"></span></summary><div class="acc__body"><p>بعد از ارسال سفارش، کد رهگیری برایتان پیامک می‌شود. در بخش «حساب کاربری» هم قابل مشاهده است.</p></div></details>
        <details class="acc" data-acc><summary>چه روش‌هایی برای پرداخت دارید؟<span class="acc__icon" aria-hidden="true"></span></summary><div class="acc__body"><p>پرداخت آنلاین با همه کارت‌های عضو شتاب از طریق درگاه زرین‌پال انجام می‌شود.</p></div></details>
      </div>
    </section>
  </div>
<?php
get_footer();
