<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zhengtuoqingdao
 * Date: 12-6-25
 * Time: 下午4:43
 * To change this template use File | Settings | File Templates.
 */
class HtmlFilter
{

    /**
     * 过滤字符串中的特殊字符
     * @static
     * @param $content
     * @return string
     */
    public static function filterSpacialHtmlChar($content)
    {

        if (empty($content)) {
            return '';
        }
        $content = self::delAllSpace($content);
        $content = self::replaceHtmlAndJs($content);

        return strip_tags($content);
    }


    /**
     * 生成摘要
     * @static
     * @param $content
     * @param $len
     * @param string $char
     * @return string
     */
    public static function getSummary($content, $len = 100, $char = 'UTF8')
    {

        if (empty($content)) {
            return '';
        }

        if ($len >= mb_strlen($content, $char)) {
            return self::filterSpacialHtmlChar($content);
        }

        return mb_substr(self::filterSpacialHtmlChar($content), 0, $len, $char) . '...';
    }

    /**
     * 去掉 $str中的特殊字符
     * @static
     * @param $document
     * @return mixed|string
     */
    public static function replaceHtmlAndJs($document)
    {
        $document = trim($document);
        if (strlen($document) <= 0) {
            return $document;
        }


        $search = array("'<script[^>]*?>.*?</script>'si");
        $replace = array("");
        $htmlCharArr = self::getSpecialHtmlArr();
        foreach ($htmlCharArr as $hc) {

            $replace[] = $hc[0];
            $search[] = "'&(" . $hc[1] . "|" . $hc[2] . "|" . $hc[3] . ");'i";

        }

        return @preg_replace($search, $replace, $document);
    }


    /**
     * 删除空格
     * @static
     * @param $str
     * @return mixed
     */
    public static function delAllSpace($str)

    {
        $preStr = array(" ", "　", "\t", "\n", "\r", "\r\n");
        $afterStr = array("", "", "", "", "", "");
        return str_replace($preStr, $afterStr, $str);
    }


    /**
     * 2012-07-05 by long
     * 特殊 Html 代码集合
     * $sh $sh[0] 显示的字符
     * $sh $sh[1] $sh[2] 显示的字符的html编码
     * $sh $sh[3] 显示的字符的注释
     * @static
     * @return array
     */
    public static function getSpecialHtmlArr()
    {

        $sh[] = array("", "nbsp", "#160", "no-break space = non-breaking space");
        $sh[] = array("¡", "iexcl", "#161", "inverted exclamation mark");
        $sh[] = array("¢", "cent", "#162", "cent sign");
        $sh[] = array("£", "pound", "#163", "pound sign");
        $sh[] = array("¤", "curren", "#164", "currency sign");
        $sh[] = array("¥", "yen", "#165", "yen sign = yuan sign");
        $sh[] = array("¦", "brvbar", "#166", "broken bar = broken vertical bar");
        $sh[] = array("§", "sect", "#167", "section sign");
        $sh[] = array("¨", "uml", "#168", "diaeresis = spacing diaeresis");
        $sh[] = array("©", "copy", "#169", "copyright sign");
        $sh[] = array("ª", "ordf", "#170", "feminine ordinal indicator");
        $sh[] = array("«", "laquo", "#171", "left-pointing double angle quotation mark = left pointing guillemet");
        $sh[] = array("¬", "not", "#172", "not sign");
        $sh[] = array("­", "shy", "#173", "soft hyphen = discretionary hyphen");
        $sh[] = array("®", "reg", "#174", "registered sign = registered trade mark sign");
        $sh[] = array("¯", "macr", "#175", "macron = spacing macron = overline = APL overbar");
        $sh[] = array("°", "deg", "#176", "degree sign");
        $sh[] = array("±", "plusmn", "#177", "plus-minus sign = plus-or-minus sign");
        $sh[] = array("²", "sup2", "#178", "superscript two = superscript digit two = squared");
        $sh[] = array("³", "sup3", "#179", "superscript three = superscript digit three = cubed");
        $sh[] = array("´", "acute", "#180", "acute accent = spacing acute");
        $sh[] = array("µ", "micro", "#181", "micro sign");
        $sh[] = array("¶", "para", "#182", "pilcrow sign = paragraph sign");
        $sh[] = array("·", "middot", "#183", "middle dot = Georgian comma = Greek middle dot");
        $sh[] = array("¸", "cedil", "#184", "cedilla = spacing cedilla");
        $sh[] = array("¹", "sup1", "#185", "superscript one = superscript digit one");
        $sh[] = array("º", "ordm", "#186", "masculine ordinal indicator");
        $sh[] = array("»", "raquo", "#187", "right-pointing double angle quotation mark = right pointing guillemet");
        $sh[] = array("¼", "frac14", "#188", "vulgar fraction one quarter = fraction one quarter");
        $sh[] = array("½", "frac12", "#189", "vulgar fraction one half = fraction one half");
        $sh[] = array("¾", "frac34", "#190", "vulgar fraction three quarters = fraction three quarters");
        $sh[] = array("¿", "iquest", "#191", "inverted question mark = turned question mark");
        $sh[] = array("À", "Agrave", "#192", "latin capital letter A with grave = latin capital letter A grave");
        $sh[] = array("Á", "Aacute", "#193", "latin capital letter A with acute");
        $sh[] = array("Â", "Acirc", "#194", "latin capital letter A with circumflex");
        $sh[] = array("Ã", "Atilde", "#195", "latin capital letter A with tilde");
        $sh[] = array("Ä", "Auml", "#196", "latin capital letter A with diaeresis");
        $sh[] = array("Å", "Aring", "#197", "latin capital letter A with ring above = latin capital letter A ring");
        $sh[] = array("Æ", "AElig", "#198", "latin capital letter AE = latin capital ligature AE");
        $sh[] = array("Ç", "Ccedil", "#199", "latin capital letter C with cedilla");
        $sh[] = array("È", "Egrave", "#200", "latin capital letter E with grave");
        $sh[] = array("É", "Eacute", "#201", "latin capital letter E with acute");
        $sh[] = array("Ê", "Ecirc", "#202", "latin capital letter E with circumflex");
        $sh[] = array("Ë", "Euml", "#203", "latin capital letter E with diaeresis");
        $sh[] = array("Ì", "Igrave", "#204", "latin capital letter I with grave");
        $sh[] = array("Í", "Iacute", "#205", "latin capital letter I with acute");
        $sh[] = array("Î", "Icirc", "#206", "latin capital letter I with circumflex");
        $sh[] = array("Ï", "Iuml", "#207", "latin capital letter I with diaeresis");
        $sh[] = array("Ð", "ETH", "#208", "latin capital letter ETH");
        $sh[] = array("Ñ", "Ntilde", "#209", "latin capital letter N with tilde");
        $sh[] = array("Ò", "Ograve", "#210", "latin capital letter O with grave");
        $sh[] = array("Ó", "Oacute", "#211", "latin capital letter O with acute");
        $sh[] = array("Ô", "Ocirc", "#212", "latin capital letter O with circumflex");
        $sh[] = array("Õ", "Otilde", "#213", "latin capital letter O with tilde");
        $sh[] = array("Ö", "Ouml", "#214", "latin capital letter O with diaeresis");
        $sh[] = array("×", "times", "#215", "multiplication sign");
        $sh[] = array("Ø", "Oslash", "#216", "latin capital letter O with stroke = latin capital letter O slash");
        $sh[] = array("Ù", "Ugrave", "#217", "latin capital letter U with grave");
        $sh[] = array("Ú", "Uacute", "#218", "latin capital letter U with acute");
        $sh[] = array("Û", "Ucirc", "#219", "latin capital letter U with circumflex");
        $sh[] = array("Ü", "Uuml", "#220", "latin capital letter U with diaeresis");
        $sh[] = array("Ý", "Yacute", "#221", "latin capital letter Y with acute");
        $sh[] = array("Þ", "THORN", "#222", "latin capital letter THORN");
        $sh[] = array("ß", "szlig", "#223", "latin small letter sharp s = ess-zed");
        $sh[] = array("à", "agrave", "#224", "latin small letter a with grave = latin small letter a grave");
        $sh[] = array("á", "aacute", "#225", "latin small letter a with acute");
        $sh[] = array("â", "acirc", "#226", "latin small letter a with circumflex");
        $sh[] = array("ã", "atilde", "#227", "latin small letter a with tilde");
        $sh[] = array("ä", "auml", "#228", "latin small letter a with diaeresis");
        $sh[] = array("å", "aring", "#229", "latin small letter a with ring above = latin small letter a ring");
        $sh[] = array("æ", "aelig", "#230", "latin small letter ae = latin small ligature ae");
        $sh[] = array("ç", "ccedil", "#231", "latin small letter c with cedilla");
        $sh[] = array("è", "egrave", "#232", "latin small letter e with grave");
        $sh[] = array("é", "eacute", "#233", "latin small letter e with acute");
        $sh[] = array("ê", "ecirc", "#234", "latin small letter e with circumflex");
        $sh[] = array("ë", "euml", "#235", "latin small letter e with diaeresis");
        $sh[] = array("ì", "igrave", "#236", "latin small letter i with grave");
        $sh[] = array("í", "iacute", "#237", "latin small letter i with acute");
        $sh[] = array("î", "icirc", "#238", "latin small letter i with circumflex");
        $sh[] = array("ï", "iuml", "#239", "latin small letter i with diaeresis");
        $sh[] = array("ð", "eth", "#240", "latin small letter eth");
        $sh[] = array("ñ", "ntilde", "#241", "latin small letter n with tilde");
        $sh[] = array("ò", "ograve", "#242", "latin small letter o with grave");
        $sh[] = array("ó", "oacute", "#243", "latin small letter o with acute");
        $sh[] = array("ô", "ocirc", "#244", "latin small letter o with circumflex");
        $sh[] = array("õ", "otilde", "#245", "latin small letter o with tilde");
        $sh[] = array("ö", "ouml", "#246", "latin small letter o with diaeresis");
        $sh[] = array("÷", "divide", "#247", "division sign");
        $sh[] = array("ø", "oslash", "#248", "latin small letter o with stroke, = latin small letter o slash");
        $sh[] = array("ù", "ugrave", "#249", "latin small letter u with grave");
        $sh[] = array("ú", "uacute", "#250", "latin small letter u with acute");
        $sh[] = array("û", "ucirc", "#251", "latin small letter u with circumflex");
        $sh[] = array("ü", "uuml", "#252", "latin small letter u with diaeresis");
        $sh[] = array("ý", "yacute", "#253", "latin small letter y with acute");
        $sh[] = array("þ", "thorn", "#254", "latin small letter thorn");
        $sh[] = array("ÿ", "yuml", "#255", "latin small letter y with diaeresis");
        $sh[] = array("ƒ", "fnof", "#402", "latin small f with hook = function = florin");
        $sh[] = array("Α", "Alpha", "#913", "greek capital letter alpha");
        $sh[] = array("Β", "Beta", "#914", "greek capital letter beta");
        $sh[] = array("Γ", "Gamma", "#915", "greek capital letter gamma");
        $sh[] = array("Δ", "Delta", "#916", "greek capital letter delta");
        $sh[] = array("Ε", "Epsilon", "#917", "greek capital letter epsilon");
        $sh[] = array("Ζ", "Zeta", "#918", "greek capital letter zeta");
        $sh[] = array("Η", "Eta", "#919", "greek capital letter eta");
        $sh[] = array("Θ", "Theta", "#920", "greek capital letter theta");
        $sh[] = array("Ι", "Iota", "#921", "greek capital letter iota");
        $sh[] = array("Κ", "Kappa", "#922", "greek capital letter kappa");
        $sh[] = array("Λ", "Lambda", "#923", "greek capital letter lambda");
        $sh[] = array("Μ", "Mu", "#924", "greek capital letter mu");
        $sh[] = array("Ν", "Nu", "#925", "greek capital letter nu");
        $sh[] = array("Ξ", "Xi", "#926", "greek capital letter xi");
        $sh[] = array("Ο", "Omicron", "#927", "greek capital letter omicron");
        $sh[] = array("Π", "Pi", "#928", "greek capital letter pi");
        $sh[] = array("Ρ", "Rho", "#929", "greek capital letter rho");
        $sh[] = array("Σ", "Sigma", "#931", "greek capital letter sigma");
        $sh[] = array("Τ", "Tau", "#932", "greek capital letter tau");
        $sh[] = array("Υ", "Upsilon", "#933", "greek capital letter upsilon");
        $sh[] = array("Φ", "Phi", "#934;", "greek capital letter phi");
        $sh[] = array("Χ", "Chi", "#935", "greek capital letter chi");
        $sh[] = array("Ψ", "Psi", "#936", "greek capital letter psi");
        $sh[] = array("Ω", "Omega", "#937", "greek capital letter omega");
        $sh[] = array("α", "alpha", "#945", "greek small letter alpha");
        $sh[] = array("β", "beta", "#946", "greek small letter beta");
        $sh[] = array("γ", "gamma", "#947", "greek small letter gamma");
        $sh[] = array("δ", "delta", "#948", "greek small letter delta");
        $sh[] = array("ε", "epsilon", "#949", "greek small letter epsilon");
        $sh[] = array("ζ", "zeta", "#950", "greek small letter zeta");
        $sh[] = array("η", "eta", "#951", "greek small letter eta");
        $sh[] = array("θ", "theta", "#952", "greek small letter theta");
        $sh[] = array("ι", "iota", "#953", "greek small letter iota");
        $sh[] = array("κ", "kappa", "#954", "greek small letter kappa");
        $sh[] = array("λ", "lambda", "#955", "greek small letter lambda");
        $sh[] = array("μ", "mu", "#956", "greek small letter mu");
        $sh[] = array("ν", "nu", "#957", "greek small letter nu");
        $sh[] = array("ξ", "xi", "#958", "greek small letter xi");
        $sh[] = array("ο", "omicron", "#959", "greek small letter omicron");
        $sh[] = array("π", "pi", "#960", "greek small letter pi");
        $sh[] = array("ρ", "rho", "#961", "greek small letter rho");
        $sh[] = array("ς", "sigmaf", "#962", "greek small letter final sigma");
        $sh[] = array("σ", "sigma", "#963", "greek small letter sigma");
        $sh[] = array("τ", "tau", "#964", "greek small letter tau");
        $sh[] = array("υ", "upsilon", "#965", "greek small letter upsilon");
        $sh[] = array("φ", "phi", "#966", "greek small letter phi");
        $sh[] = array("χ", "chi", "#967", "greek small letter chi");
        $sh[] = array("ψ", "psi", "#968", "greek small letter psi");
        $sh[] = array("ω", "omega", "#969", "greek small letter omega");
        $sh[] = array("ϑ", "thetasym", "#977", "greek small letter theta symbol");
        $sh[] = array("ϒ", "upsih", "#978", "greek upsilon with hook symbol");
        $sh[] = array("ϖ", "piv", "#982", "greek pi symbol");
        $sh[] = array("•", "bull", "#8226", "bullet = black small circle");
        $sh[] = array("…", "hellip", "#8230", "horizontal ellipsis = three dot leader");
        $sh[] = array("′", "prime", "#8242", "prime = minutes = feet");
        $sh[] = array("″", "Prime", "#8243", "double prime = seconds = inches");
        $sh[] = array("‾", "oline", "#8254", "overline = spacing overscore");
        $sh[] = array("⁄", "frasl", "#8260", "fraction slash");
        $sh[] = array("℘", "weierp", "#8472", "script capital P = power set = Weierstrass p");
        $sh[] = array("ℑ", "image", "#8465", "blackletter capital I = imaginary part");
        $sh[] = array("ℜ", "real", "#8476", "blackletter capital R = real part symbol");
        $sh[] = array("™", "trade", "#8482", "trade mark sign");
        $sh[] = array("ℵ", "alefsym", "#8501", "alef symbol = first transfinite cardinal");
        $sh[] = array("←", "larr", "#8592", "leftwards arrow");
        $sh[] = array("↑", "uarr", "#8593", "upwards arrow");
        $sh[] = array("→", "rarr", "#8594", "rightwards arrow");
        $sh[] = array("↓", "darr", "#8595", "downwards arrow");
        $sh[] = array("↔", "harr", "#8596", "left right arrow");
        $sh[] = array("↵", "crarr", "#8629", "downwards arrow with corner leftwards = carriage return");
        $sh[] = array("⇐", "lArr", "#8656", "leftwards double arrow");
        $sh[] = array("⇑", "uArr", "#8657", "upwards double arrow");
        $sh[] = array("⇒", "rArr", "#8658", "rightwards double arrow");
        $sh[] = array("⇓", "dArr", "#8659", "downwards double arrow");
        $sh[] = array("⇔", "hArr", "#8660", "left right double arrow");
        $sh[] = array("∀", "forall", "#8704", "for all");
        $sh[] = array("∂", "part", "#8706", "partial differential");
        $sh[] = array("∃", "exist", "#8707", "there exists");
        $sh[] = array("∅", "empty", "#8709", "empty set = null set = diameter");
        $sh[] = array("∇", "nabla", "#8711", "nabla = backward difference");
        $sh[] = array("∈", "isin", "#8712", "element of");
        $sh[] = array("∉", "notin", "#8713", "not an element of");
        $sh[] = array("∋", "ni", "#8715", "contains as member");
        $sh[] = array("∏", "prod", "#8719", "n-ary product = product sign");
        $sh[] = array("∑", "sum", "#8721", "n-ary sumation");
        $sh[] = array("−", "minus", "#8722", "minus sign");
        $sh[] = array("∗", "lowast", "#8727", "asterisk operator");
        $sh[] = array("√", "radic", "#8730", "square root = radical sign");
        $sh[] = array("∝", "prop", "#8733", "proportional to");
        $sh[] = array("∞", "infin", "#8734", "infinity");
        $sh[] = array("∠", "ang", "#8736", "angle");
        $sh[] = array("∧", "and", "#8743", "logical and = wedge");
        $sh[] = array("∨", "or", "#8744", "logical or = vee");
        $sh[] = array("∩", "cap", "#8745", "intersection = cap");
        $sh[] = array("∪", "cup", "#8746", "union = cup");
        $sh[] = array("∫", "int", "#8747", "integral");
        $sh[] = array("∴", "there4", "#8756", "therefore");
        $sh[] = array("∼", "sim", "#8764", "tilde operator = varies with = similar to");
        $sh[] = array("≅", "cong", "#8773", "approximately equal to");
        $sh[] = array("≈", "asymp", "#8776", "almost equal to = asymptotic to");
        $sh[] = array("≠", "ne", "#8800", "not equal to");
        $sh[] = array("≡", "equiv", "#8801", "identical to");
        $sh[] = array("≤", "le", "#8804", "less-than or equal to");
        $sh[] = array("≥", "ge", "#8805", "greater-than or equal to");
        $sh[] = array("⊂", "sub", "#8834", "subset of");
        $sh[] = array("⊃", "sup", "#8835", "superset of");
        $sh[] = array("⊄", "nsub", "#8836", "not a subset of");
        $sh[] = array("⊆", "sube", "#8838", "subset of or equal to");
        $sh[] = array("⊇", "supe", "#8839", "superset of or equal to");
        $sh[] = array("⊕", "oplus", "#8853", "circled plus = direct sum");
        $sh[] = array("⊗", "otimes", "#8855", "circled times = vector product");
        $sh[] = array("⊥", "perp", "#8869", "up tack = orthogonal to = perpendicular");
        $sh[] = array("⋅", "sdot", "#8901", "dot operator");
        $sh[] = array("⌈", "lceil", "#8968", "left ceiling = apl upstile");
        $sh[] = array("⌉", "rceil", "#8969", "right ceiling");
        $sh[] = array("⌊", "lfloor", "#8970", "left floor = apl downstile");
        $sh[] = array("⌋", "rfloor", "#8971", "right floor");
        $sh[] = array("〈", "lang", "#9001", "left-pointing angle bracket = bra");
        $sh[] = array("〉", "rang", "#9002", "right-pointing angle bracket = ket");
        $sh[] = array("◊", "loz", "#9674", "lozenge");
        $sh[] = array("♠", "spades", "#9824", "black spade suit");
        $sh[] = array("♣", "clubs", "#9827", "black club suit = shamrock");
        $sh[] = array("♥", "hearts", "#9829", "black heart suit = valentine");
        $sh[] = array("♦", "diams", "#9830", "black diamond suit");
        $sh[] = array('"', "quot", "#34", "quotation mark = APL quote");
        $sh[] = array("&", "amp", "#38", "ampersand");
        $sh[] = array("<", "lt", "#60", "less-than sign");
        $sh[] = array(">", "gt", "#62", "greater-than sign");
        $sh[] = array("Œ", "OElig", "#338", "latin capital ligature OE");
        $sh[] = array("œ", "oelig", "#339", "latin small ligature oe");
        $sh[] = array("Š", "Scaron", "#352", "latin capital letter S with caron");
        $sh[] = array("š", "scaron", "#353", "latin small letter s with caron");
        $sh[] = array("Ÿ", "Yuml", "#376", "latin capital letter Y with diaeresis");
        $sh[] = array("ˆ", "circ", "#710", "modifier letter circumflex accent");
        $sh[] = array('', "tilde", "#732", "small tilde");
        $sh[] = array(" ", "ensp", "#8194", "en space");
        $sh[] = array(" ", "emsp", "#8195", "em space");
        $sh1[] = array(" ", "thinsp", "#8201", "thin space");
        $sh[] = array("–", "ndash", "#8211", "en dash");
        $sh[] = array("—", "mdash", "#8212", "em dash");
        $sh[] = array("‘", "lsquo", "#8216", "left single quotation mark");
        $sh[] = array("’", "rsquo", "#8217", "right single quotation mark");
        $sh[] = array("‚", "sbquo", "#8218", "single low-9 quotation mark");
        $sh[] = array("“", "ldquo", "#8220", "left double quotation mark");
        $sh[] = array("”", "rdquo", "#8221", "right double quotation mark");
        $sh[] = array("„", "bdquo", "#8222", "double low-9 quotation mark");
        $sh[] = array("†", "dagger", "#8224", "dagger");
        $sh[] = array("‡", "Dagger", "#8225", "double dagger");
        $sh[] = array("‰", "permil", "#8240", "per mille sign");
        $sh[] = array("‹", "lsaquo", "#8249", "single left-pointing angle quotation mark");
        $sh[] = array("›", "rsaquo", "#8250", "single right-pointing angle quotation mark");
        $sh[] = array("€", "euro", "#8364", "euro sign");
        $sh[] = array("„", "dbquo;", "#132;");

        return $sh;
    }

    /**
     * 获取发起访问的浏览器信息
     * @static
     * @return string
     */
    public static function getRequestBrowserVersion()
    {

        $hrh = $_SERVER["HTTP_USER_AGENT"];
        if (strpos($hrh, "MSIE 9.0") > -1) {
            return "IE 9.0";
        }
        if (strpos($hrh, "MSIE 8.0") > -1) {
            return "IE 8.0";
        }
        if (strpos($hrh, "MSIE 7.0") > -1) {
            return "IE 7.0";
        }

        if (strpos($hrh, "MSIE 6.0") > -1) {
            return "IE 6.0";
        }

        if (strpos($hrh, "MSIE 5.0") > -1) {
            return "IE 5.0";
        }
        if (strpos($hrh, "Firefox/3") > -1) {
            return "Firefox/3";
        }

        if (strpos($hrh, "Firefox/3") > -1) {
            return "Firefox/3";
        }

        if (strpos($hrh, "Firefox/2") > -1) {
            return "Firefox/2";
        }

        if (strpos($hrh, "Chrome") > -1) {
            return "Chrome";
        }

        if (strpos($hrh, "Opera") > -1) {
            return "Opera";
        }

        //微信内置浏览器：
        if (strpos($hrh, "MicroMessenger") > -1) {
            return "MicroMessenger";
        }

        return $hrh;
    }

    public static function isFromIPhone(){
        $hrh = $_SERVER["HTTP_USER_AGENT"];

        //从IPhone或同类设备（例如IPod）来的访问：
        if (strpos($hrh, "iPhone OS") > -1) {
            return true;
        }

        return false;
    }

    public static function isFromAndroid(){
        $hrh = $_SERVER["HTTP_USER_AGENT"];

        //从Android设备来的访问：
        if (strpos($hrh, "Android") > -1) {
            return true;
        }

        return false;
    }

    /**
     * PHP获取浏览器语言
     * @static
     * @return string
     */
    public static function getRequestBrowserLang()
    {

        //只取前4位，这样只判断最优先的语言。如果取前5位，可能出现en,zh的情况，影响判断。
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4);
        if (preg_match("/zh-c/i", $lang))
            return "简体中文";
        else if (preg_match("/zh/i", $lang))
            return "繁體中文";
        else if (preg_match("/en/i", $lang))
            return "English";
        else if (preg_match("/fr/i", $lang))
            return "French";
        else if (preg_match("/de/i", $lang))
            return "German";
        else if (preg_match("/jp/i", $lang))
            return "Japanese";
        else if (preg_match("/ko/i", $lang))
            return "Korean";
        else if (preg_match("/es/i", $lang))
            return "Spanish";
        else if (preg_match("/sv/i", $lang))
            return "Swedish";
        else return $_SERVER["HTTP_ACCEPT_LANGUAGE"];

    }

    /**
     * @static
     * @param $url
     * @param $dataArray
     * @return bool
     */
    public static function callWebApiByPost($url, $dataArray)
    {
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
       //curl_setopt($ch, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, true);

        $paramStr = '';
        foreach($dataArray as $key=>$value)
        {
            $paramStr .= $key . '=' . urlencode($value);
            $paramStr .= '&';
        }
        $paramStr = rtrim($paramStr , '&');
        //Yii::log('加工后的参数为：' . $paramStr , 'warning');

        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $handles = curl_exec($ch);
        curl_close($ch);
        return $handles;
    }

    public static function paraTagStr($str, $enclosure = false, $pTag = false) {

        if($pTag)
            $paraTag = "</p><p>";
        else
            $paraTag = "<br>";

        $taggedStr = str_replace(PHP_EOL, $paraTag, $str);

        if($enclosure) {
            $taggedStr = "<p>" . $taggedStr . "</p>";
        }

        return $taggedStr;
    }

    public static function callUrl($url) {
        $ch = curl_init();

        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,30);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * 模拟提交url，支持https提交 可用于各类api请求
     * @param string $url ： 提交的地址（无需带参数）
     * @param array $data :参数数组 参数名 => 参数值
     * @param string $method : POST/GET，默认GET方式
     * @return mixed
     */
    public static function callHttp($url, $data=array(), $method='GET'){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer

        if($method=='POST'){
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            if (is_array($data) && count($data) > 0){
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
            } elseif( isset($data) ) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode($data)); // Post提交json格式的数据包
            }
        } else {
            //说明是get 方式，将参数自动添加到url中：
            if (is_array($data) && count($data) > 0){
                $url = $url.'?';
                foreach($data as $field=>$value) {
                    $url = $url . $field . '=' . $value. '&';
                }

                $url = trim($url, '&');
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }

    public static function remoteIpOfUser($serverVar) {
        $user_IP = ($serverVar["HTTP_VIA"]) ?
            $serverVar["HTTP_X_FORWARDED_FOR"] : $serverVar["REMOTE_ADDR"];
        $user_IP = ($user_IP) ? $user_IP : $serverVar["REMOTE_ADDR"];

        return TextUtility::getFirstElement($user_IP, ',');

    }

}
