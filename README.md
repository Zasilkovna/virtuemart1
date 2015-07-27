<h1>Modul pro VirtueMart</h1>
<h2>Instalace</h2>
<ol style="color: black; ">
  <li><a href="http://www.zasilkovna.cz/soubory/virtuemart-module.zip">Stáhnout soubor modulu »</a></li>
  <li>
    Přihlašte se do administrace Joomly, otevřete nabídku Extensions a Install/Uninstall<br><br>
    <a href="https://cloud.githubusercontent.com/assets/13521096/8906978/2a65f8b6-3473-11e5-8ab2-7e25664329b6.jpg"><img src="https://cloud.githubusercontent.com/assets/13521096/8906978/2a65f8b6-3473-11e5-8ab2-7e25664329b6.jpg"></a><br><br>
  </li>
  <li>
    Mělo by se zobrazit hlášení o úspěšné instalaci, bude nutné ještě provést nastavení api klíče a další.<br><br>    
  </li>
  <li>
    Nastavení modulu dopravce Virtuemartu se provede v nabídce Virtuemartu pod záložkou Store -> Shipping module list. Vyberte konfiguraci modulu Zásilkovna.cz.<br>
    <a href="https://cloud.githubusercontent.com/assets/13521096/8906979/2a66eeba-3473-11e5-8228-9ac978d0b26c.jpg"><img width=600 height=369 src="https://cloud.githubusercontent.com/assets/13521096/8906979/2a66eeba-3473-11e5-8228-9ac978d0b26c.jpg"></a><br><br>
  </li>
  <li>
    V nastavení je nutné zadat klíč API. Váš klíč API je <code>41494564a70d6de6</code> a v případě potřeby jej najdete také ve své klientské sekci, pod <strong><em>Můj účet</em></strong>:<br> Dále je nutné povolit či zákázat cílové země zásilkovny, zadat cenu dopravy a další.  Nastavení uložíte kliknutím na tlačítko Save v pravém horním rohu administrace.<br>
    <a href="https://cloud.githubusercontent.com/assets/13521096/8906981/2a67eb3a-3473-11e5-8ece-0fb1c05a66aa.jpg"><img width=600 height=620 src="https://cloud.githubusercontent.com/assets/13521096/8906981/2a67eb3a-3473-11e5-8ece-0fb1c05a66aa.jpg"></a><br><br>
  </li>
  <li>
    Pokud chcete omezit výběr platebních metod pro konkrétní dopravce (např. dobírka pro Zásilkovnu má mít rozdílnou cenu než dobírka přes jiného dopravce), můžete tak učinit v Omezení způsobu platby (V hlavní nabídce zvolit Components -> Zasilkovna -> Omezení způsobu platby). Automaticky jsou všechny kombinace povoleny. Nejprve je však nutné ručně upravit systémový soubor Virtuemartu dle instrukcí. Když vše provedete správně, budete informování o funkčnosti Omezení platby.<br><br>
    <a href="https://cloud.githubusercontent.com/assets/13521096/8906980/2a677da8-3473-11e5-8557-9fccac29adf0.jpg"><img width=600 height=450 src="https://cloud.githubusercontent.com/assets/13521096/8906980/2a677da8-3473-11e5-8557-9fccac29adf0.jpg"></a><br></a><br>    
  </li>
  <li>
    Pokud máte vše nastaveno, je třeba povolit modul Zásilkovna, aby se zobrazoval při výběru dopravy vašim zákazníkům. Učiníte tak v záložce Configuration -> Shipping.<br><br>
    <a href="https://cloud.githubusercontent.com/assets/13521096/8906976/2a646ab4-3473-11e5-8da2-40da54a1354e.jpg"><img width=600 height=350 src="https://cloud.githubusercontent.com/assets/13521096/8906976/2a646ab4-3473-11e5-8da2-40da54a1354e.jpg"></a><br><br>
    Poté již můžete modul Zásilkovna plně využívat.<br><br>
  </li>  
  <li>
    Dále až budete mít nějaké objednávky se způsobem dopravy Zásilkovna, můžete si je exportovat v CSV formátu pro hromadné podání zásilek:<br><br>
    <a href="https://cloud.githubusercontent.com/assets/13521096/8906977/2a65c45e-3473-11e5-9579-dd1cd0e47f48.jpg"><img width=600 height=348 src="https://cloud.githubusercontent.com/assets/13521096/8906977/2a65c45e-3473-11e5-9579-dd1cd0e47f48.jpg"></a><br><br>
  </li>
</ol>
<h2>Informace o modulu</h2>
<p>Podporované jazyky:</p>
<ul>
<li>čeština</li>
<li>angličtina</li>
</ul>
<p>Podporované verze VirtueMartu:</p>
<ul>
  <li>1.1.x</li>
  <li>Při problému s použitím v jiné verzi nás kontaktujte na adrese <a href="mailto:technicka.podpora@zasilkovna.cz">technicka.podpora@zasilkovna.cz</a></li>
  <li>Modul pro Virtuemart 2.0 je dostupný <a href="http://www.zasilkovna.cz/virtuemart2">zde</a></li>
</ul>
<p>Poskytované funkce:</p>
<ul>
  <li>Instalace typu dopravního modulu Zásilkovna
    <ul>
      <li>možnost rozlišení ceny dle cílové země</li>
      <li>volba typu zobrazení stejná jako v <a href="http://www.zasilkovna.cz/pristup-k-pobockam/pruvodce">průvodci vložením poboček (JS API)</a></li>
      <li>vybraná pobočka se zobrazuje v detailu objednávky v uživatelské (front-office) i administrátorské (back-office) sekci</li>
    </ul>
  </li>
  <li>Možnost exportu souboru s objednávkami
    <ul>
      <li>možnost označit objednávky, export CSV souboru pro hromadné podání zásilek</li>
      <li>vyznačení již exportovaných objednávek</li>
      <li>automatické a manuální označení dobírek</li>
    </ul>
  </li>
</ul>
