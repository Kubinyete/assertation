<?php

namespace Kubinyete\Assertation;

use DateTimeInterface;
use Kubinyete\Assertation\Exception\ValidationException;
use Kubinyete\Assertation\Util\ArrayUtil;
use Kubinyete\Assertation\Util\Luhn;
use UnexpectedValueException;

class AssertBuilder
{
    protected Assert $context;
    protected $value;
    protected ?string $attribute;
    protected bool $sensitive;
    protected ?AssertBuilder $parent;

    protected array $checks;
    protected array $errors;

    public function __construct(
        Assert $assertContext,
        $value,
        ?string $attribute,
        bool $sensitive,
        ?AssertBuilder $parent = null,
        ?array $checks = null,
        ?array $errors = null
    ) {
        $this->context = $assertContext;
        $this->value = $value;
        $this->attribute = $attribute;
        $this->sensitive = $sensitive;
        $this->parent = $parent;

        $this->checks = $checks ?? [];
        $this->errors = $errors ?? [];
    }

    //

    protected function clone(
        $value,
        ?AssertBuilder $overrideParent = null,
        ?string $overrideAttribute = null,
        ?bool $overrideSensitive = null,
        bool $inheritCheckData = true
    ): static {
        return new static(
            $this->context,
            $value,
            $overrideAttribute ?? $this->attribute,
            $overrideSensitive ?? $this->sensitive,
            $overrideParent ?? $this->parent,
            $inheritCheckData ? $this->checks : null,
            $inheritCheckData ? $this->errors : null
        );
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function getSensitive(): bool
    {
        return $this->sensitive;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function expressionChecks(): array
    {
        return $this->checks;
    }

    public function expressionErrors(): array
    {
        return array_map(fn ($x) => $x[1], $this->errors);
    }

    public function expressionHasErrors(): bool
    {
        return !empty($this->errors);
    }

    //

    public function valid(): bool
    {
        return !$this->expressionHasErrors() || ($this->parent ? $this->parent->valid() : false);
    }

    public function errors(): array
    {
        $errors = [];

        if (!$this->valid()) {
            $curr = $this;

            do {
                $errors = array_merge($errors, $curr->expressionErrors());
            } while ($curr = $curr->parent);
        }

        return $errors;
    }

    public function hasErrors(): bool
    {
        return !$this->valid();
    }

    public function get()
    {
        return !$this->expressionHasErrors() ? $this->getValue() : ($this->parent ? $this->parent->get() : null);
    }

    public function getOriginal()
    {
        return $this->parent ? $this->parent->getOriginal() : $this->getValue();
    }

    public function validate()
    {
        if (!$this->valid()) {
            $errors = $this->errors();
            $attr = $this->attribute ?? 'general';

            throw new ValidationException([$attr => $errors]);
        }

        return $this;
    }

    //

    public function or(): static
    {
        return $this->clone($this->getOriginal(), $this, null, null, false);
    }

    public function assert($cond, ?string $message, string $ruleName, array $additionalContext = []): static
    {
        $cond = boolval($cond);

        $pre = $this->attribute ? "{$this->attribute}: " : '';
        $message ??= $ruleName;

        // dump("$ruleName($this->attribute)", $cond);

        if (!$cond) {
            $message = $pre . $this->context->translate($message, array_merge([
                'rule' => $ruleName,
                'result' => $cond,
                'value' => $this->sensitive ? $this->value : null,
                'attribute' => $this->attribute,
                'sensitive' => $this->sensitive,
            ], $additionalContext));

            if ($this->context->shouldThrowEarly()) {
                throw new UnexpectedValueException($message);
            }

            $this->errors[] = [$cond, $message];
        }

        $this->checks[] = [$cond, $message];
        return $this;
    }

    public function eq($x, ?string $message = null): static
    {
        return $this->assert($this->value == $x, $message, __FUNCTION__, compact('x'));
    }

    public function seq($x, ?string $message = null): static
    {
        return $this->assert($this->value === $x, $message, __FUNCTION__, compact('x'));
    }

    public function between($x, $y, ?string $message = null): static
    {
        return $this->assert($this->value >= $x && $this->value <= $y, $message, __FUNCTION__, compact('x', 'y'));
    }

    public function lbetween(int $x, int $y, ?string $message = null): static
    {
        $length = strlen($this->value);
        return $this->assert($length >= $x && $length <= $y, $message, __FUNCTION__, compact('x', 'y'));
    }

    public function array(): static
    {
        return $this->assert(is_array($this->value), null, __FUNCTION__);
    }

    public function string(): static
    {
        return $this->assert(is_string($this->value), null, __FUNCTION__);
    }

    public function boolean(): static
    {
        return $this->assert(is_bool($this->value), null, __FUNCTION__);
    }

    public function integer(): static
    {
        return $this->assert(is_integer($this->value), null, __FUNCTION__);
    }

    public function numeric(): static
    {
        return $this->assert(is_numeric($this->value), null, __FUNCTION__);
    }

    public function float(): static
    {
        return $this->assert(is_float($this->value), null, __FUNCTION__);
    }

    public function null(): static
    {
        return $this->assert(is_null($this->value), null, __FUNCTION__);
    }

    public function req(): static
    {
        return $this->assert(!is_null($this->value), null, __FUNCTION__);
    }

    public function gt($x, ?string $message = null): static
    {
        return $this->assert($this->value > $x, $message, __FUNCTION__, compact('x'));
    }

    public function gte($x, ?string $message = null): static
    {
        return $this->assert($this->value >= $x, $message, __FUNCTION__, compact('x'));
    }

    public function lt($x, ?string $message = null): static
    {
        return $this->assert($this->value < $x, $message, __FUNCTION__, compact('x'));
    }

    public function lte($x, ?string $message = null): static
    {
        return $this->assert($this->value <= $x, $message, __FUNCTION__, compact('x'));
    }

    public function lgt($x, ?string $message = null): static
    {
        return $this->assert(strlen($this->value) > $x, $message, __FUNCTION__, compact('x'));
    }

    public function lgte($x, ?string $message = null): static
    {
        return $this->assert(strlen($this->value) >= $x, $message, __FUNCTION__, compact('x'));
    }

    public function llt($x, ?string $message = null): static
    {
        return $this->assert(strlen($this->value) < $x, $message, __FUNCTION__, compact('x'));
    }

    public function llte($x, ?string $message = null): static
    {
        return $this->assert(strlen($this->value) <= $x, $message, __FUNCTION__, compact('x'));
    }

    public function in(array $x, ?string $message = null, bool $strict = false): static
    {
        return $this->assert(in_array($this->value, $x, $strict), $message, __FUNCTION__, ['x' => implode(', ', $x)]);
    }

    public function oneOf(array $x, ?string $message = null, bool $strict = false): static
    {
        return $this->in($x, $message, $strict);
    }

    public function date(): static
    {
        return $this->assert($this->value instanceof DateTimeInterface, null, __FUNCTION__);
    }

    public function email(): static
    {
        return $this->assert(filter_var($this->value, FILTER_VALIDATE_EMAIL), null, __FUNCTION__);
    }

    public function ip(): static
    {
        return $this->assert(filter_var($this->value, FILTER_VALIDATE_IP), null, __FUNCTION__);
    }

    public function ipv4(): static
    {
        return $this->assert(filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4), null, __FUNCTION__);
    }

    public function ipv6(): static
    {
        return $this->assert(filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6), null, __FUNCTION__);
    }

    public function domain(): static
    {
        return $this->assert(filter_var($this->value, FILTER_VALIDATE_DOMAIN), null, __FUNCTION__);
    }

    public function url(): static
    {
        return $this->assert(filter_var($this->value, FILTER_VALIDATE_URL), null, __FUNCTION__);
    }

    protected function pattern(string $pattern, string $ruleNmae): static
    {
        return $this->assert(boolval(preg_match($pattern, $this->value)), null, $ruleName);
    }

    public function digits(): static
    {
        return $this->pattern('/^[0-9]+$/', __FUNCTION__);
    }

    public function alpha(string $including = ''): static
    {
        return $this->pattern("/^[a-zA-Z$including ]+$/", __FUNCTION__);
    }

    public function alphanumeric(string $including = ''): static
    {
        return $this->pattern("/^[a-zA-Z0-9$including ]+$/", __FUNCTION__);
    }

    public function currency(): static
    {
        static $currencies = ['AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'SSP', 'STD', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW'];
        return $this->assert(array_search($this->value, $currencies) !== false, null, __FUNCTION__);
    }

    public function country2(): static
    {
        static $countries = ['AF', 'AX', 'AL', 'DZ', 'AS', 'AD', 'AO', 'AI', 'AQ', 'AG', 'AR', 'AM', 'AW', 'AU', 'AT', 'AZ', 'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BQ', 'BA', 'BW', 'BV', 'BR', 'IO', 'BN', 'BG', 'BF', 'BI', 'CV', 'KH', 'CM', 'CA', 'KY', 'CF', 'TD', 'CL', 'CN', 'CX', 'CC', 'CO', 'KM', 'CG', 'CD', 'CK', 'CR', 'CI', 'HR', 'CU', 'CW', 'CY', 'CZ', 'DK', 'DJ', 'DM', 'DO', 'EC', 'EG', 'SV', 'GQ', 'ER', 'EE', 'ET', 'SZ', 'FK', 'FO', 'FJ', 'FI', 'FR', 'GF', 'PF', 'TF', 'GA', 'GM', 'GE', 'DE', 'GH', 'GI', 'GR', 'GL', 'GD', 'GP', 'GU', 'GT', 'GG', 'GN', 'GW', 'GY', 'HT', 'HM', 'VA', 'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IR', 'IQ', 'IE', 'IM', 'IL', 'IT', 'JM', 'JP', 'JE', 'JO', 'KZ', 'KE', 'KI', 'KP', 'KR', 'KW', 'KG', 'LA', 'LV', 'LB', 'LS', 'LR', 'LY', 'LI', 'LT', 'LU', 'MO', 'MK', 'MG', 'MW', 'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU', 'YT', 'MX', 'FM', 'MD', 'MC', 'MN', 'ME', 'MS', 'MA', 'MZ', 'MM', 'NA', 'NR', 'NP', 'NL', 'NC', 'NZ', 'NI', 'NE', 'NG', 'NU', 'NF', 'MP', 'NO', 'OM', 'PK', 'PW', 'PS', 'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PL', 'PT', 'PR', 'QA', 'RE', 'RO', 'RU', 'RW', 'BL', 'SH', 'KN', 'LC', 'MF', 'PM', 'VC', 'WS', 'SM', 'ST', 'SA', 'SN', 'RS', 'SC', 'SL', 'SG', 'SX', 'SK', 'SI', 'SB', 'SO', 'ZA', 'GS', 'SS', 'ES', 'LK', 'SD', 'SR', 'SJ', 'SE', 'CH', 'SY', 'TW', 'TJ', 'TZ', 'TH', 'TL', 'TG', 'TK', 'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV', 'UG', 'UA', 'AE', 'GB', 'US', 'UM', 'UY', 'UZ', 'VU', 'VE', 'VN', 'VG', 'VI', 'WF', 'EH', 'YE', 'ZM', 'ZW'];
        return $this->assert(array_search($this->value, $countries) !== false, null, __FUNCTION__);
    }

    public function country3(): static
    {
        static $countries = ['AFG', 'ALA', 'ALB', 'DZA', 'ASM', 'AND', 'AGO', 'AIA', 'ATA', 'ATG', 'ARG', 'ARM', 'ABW', 'AUS', 'AUT', 'AZE', 'BHS', 'BHR', 'BGD', 'BRB', 'BLR', 'BEL', 'BLZ', 'BEN', 'BMU', 'BTN', 'BOL', 'BES', 'BIH', 'BWA', 'BVT', 'BRA', 'IOT', 'BRN', 'BGR', 'BFA', 'BDI', 'CPV', 'KHM', 'CMR', 'CAN', 'CYM', 'CAF', 'TCD', 'CHL', 'CHN', 'CXR', 'CCK', 'COL', 'COM', 'COG', 'COD', 'COK', 'CRI', 'CIV', 'HRV', 'CUB', 'CUW', 'CYP', 'CZE', 'DNK', 'DJI', 'DMA', 'DOM', 'ECU', 'EGY', 'SLV', 'GNQ', 'ERI', 'EST', 'ETH', 'SWZ', 'FLK', 'FRO', 'FJI', 'FIN', 'FRA', 'GUF', 'PYF', 'ATF', 'GAB', 'GMB', 'GEO', 'DEU', 'GHA', 'GIB', 'GRC', 'GRL', 'GRD', 'GLP', 'GUM', 'GTM', 'GGY', 'GIN', 'GNB', 'GUY', 'HTI', 'HMD', 'VAT', 'HND', 'HKG', 'HUN', 'ISL', 'IND', 'IDN', 'IRN', 'IRQ', 'IRL', 'IMN', 'ISR', 'ITA', 'JAM', 'JPN', 'JEY', 'JOR', 'KAZ', 'KEN', 'KIR', 'PRK', 'KOR', 'KWT', 'KGZ', 'LAO', 'LVA', 'LBN', 'LSO', 'LBR', 'LBY', 'LIE', 'LTU', 'LUX', 'MAC', 'MKD', 'MDG', 'MWI', 'MYS', 'MDV', 'MLI', 'MLT', 'MHL', 'MTQ', 'MRT', 'MUS', 'MYT', 'MEX', 'FSM', 'MDA', 'MCO', 'MNG', 'MNE', 'MSR', 'MAR', 'MOZ', 'MMR', 'NAM', 'NRU', 'NPL', 'NLD', 'NCL', 'NZL', 'NIC', 'NER', 'NGA', 'NIU', 'NFK', 'MNP', 'NOR', 'OMN', 'PAK', 'PLW', 'PSE', 'PAN', 'PNG', 'PRY', 'PER', 'PHL', 'PCN', 'POL', 'PRT', 'PRI', 'QAT', 'REU', 'ROU', 'RUS', 'RWA', 'BLM', 'SHN', 'KNA', 'LCA', 'MAF', 'SPM', 'VCT', 'WSM', 'SMR', 'STP', 'SAU', 'SEN', 'SRB', 'SYC', 'SLE', 'SGP', 'SXM', 'SVK', 'SVN', 'SLB', 'SOM', 'ZAF', 'SGS', 'SSD', 'ESP', 'LKA', 'SDN', 'SUR', 'SJM', 'SWE', 'CHE', 'SYR', 'TWN', 'TJK', 'TZA', 'THA', 'TLS', 'TGO', 'TKL', 'TON', 'TTO', 'TUN', 'TUR', 'TKM', 'TCA', 'TUV', 'UGA', 'UKR', 'ARE', 'GBR', 'USA', 'UMI', 'URY', 'UZB', 'VUT', 'VEN', 'VNM', 'VGB', 'VIR', 'WLF', 'ESH', 'YEM', 'ZMB', 'ZWE'];
        return $this->assert(array_search($this->value, $countries) !== false, null, __FUNCTION__);
    }

    public function decimal(): static
    {
        return $this->pattern('/^[0-9]*(\.[0-9]+)?$/', __FUNCTION__);
    }

    public function cardNumber(): static
    {
        if (!is_numeric($this->value)) {
            return $this->assert(false, null, __FUNCTION__);
        }

        return $this->assert(Luhn::check($this->value), null, __FUNCTION__);
    }

    // @NOTE:
    // Modifiers

    public function asCpf(bool $format = true): static
    {
        $raw = preg_replace('/[^0-9]/', '', strval($this->value));
        $raw = str_pad($raw, 11, '0', STR_PAD_LEFT);

        if (strlen($raw) != 11) return $this->assert(false, null, __FUNCTION__);

        $split = str_split($raw);
        $initial = $split[0];

        if (array_reduce($split, fn ($prev, $curr) => $curr != $initial ? $curr : $prev, $initial) == $initial) return $this->assert(false, null, __FUNCTION__);

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $raw[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($raw[$c] != $d) {
                return $this->assert(false, null, __FUNCTION__);
            }
        }

        $p1 = substr($raw, 0, 3);
        $p2 = substr($raw, 3, 3);
        $p3 = substr($raw, 6, 3);
        $p4 = substr($raw, 9, 2);

        return $this->clone($format ? "$p1.$p2.$p3-$p4" : $raw);
    }

    public function asCnpj(bool $format = true): static
    {
        $raw = preg_replace('/[^0-9]/', '', strval($this->value));
        $raw = str_pad($raw, 14, '0', STR_PAD_LEFT);

        if (strlen($raw) != 14) return $this->assert(false, null, __FUNCTION__);

        $split = str_split($raw);
        $initial = $split[0];

        if (array_reduce($split, fn ($prev, $curr) => $curr != $initial ? $curr : $prev, $initial) == $initial) return $this->assert(false, null, __FUNCTION__);

        $j = 5;
        $k = 6;
        $soma1 = 0;
        $soma2 = 0;

        for ($i = 0; $i < 13; $i++) {
            $j = $j == 1 ? 9 : $j;
            $k = $k == 1 ? 9 : $k;

            $soma2 += ($raw[$i] * $k);

            if ($i < 12) {
                $soma1 += ($raw[$i] * $j);
            }

            $k--;
            $j--;
        }

        $digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
        $digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;

        if (!(($raw[12] == $digito1) and ($raw[13] == $digito2))) return $this->assert(false, null, __FUNCTION__);

        $p1 = substr($raw, 0, 2);
        $p2 = substr($raw, 2, 3);
        $p3 = substr($raw, 5, 3);
        $p4 = substr($raw, 8, 4);
        $p5 = substr($raw, 12, 2);

        return $this->clone($format ? "$p1.$p2.$p3/$p4-$p5" : $raw);
    }

    public function asTrim(): static
    {
        return $this->clone(trim(strval($this->value)));
    }

    public function asUppercase(): static
    {
        return $this->clone(mb_strtoupper(strval($this->value)));
    }

    public function asLowercase(): static
    {
        return $this->clone(mb_strtolower(strval($this->value)));
    }

    public function asDecimal(array $decimalSymbol = [',', '.']): static
    {
        $data = str_replace($decimalSymbol, '.', strval($this->value));
        return $this->clone($data)->decimal();
    }

    public function asExtract(string $charClass): static
    {
        return $this->clone(preg_replace("/[^$charClass]/", '', strval($this->value)));
    }

    public function asDigitsOnly(): static
    {
        return $this->asExtract('0-9');
    }

    public function asTruncate(int $size, string $end = '...'): static
    {
        $data = strval($this->value);
        $dataSize = strlen($data);
        $endSize = strlen($end);

        if ($dataSize > $size) {
            $size -= $endSize;
        }

        return $this->clone(substr($data, 0, $size) . $end);
    }

    public function asLimit(int $size): static
    {
        return $this->asTruncate($size, '');
    }

    public function asCardNumber(): static
    {
        return $this->asDigitsOnly()->cardNumber();
    }

    //

    public function rules(string $rules): static
    {
        $builder = $this;
        $contexts = explode('|', $rules);
        $context = array_shift($contexts);

        do {
            $rules = explode('.', $context);

            foreach ($rules as $rule) {
                $pieces = explode(':', $rule, 2);
                $rule = array_shift($pieces);
                $args = array_shift($pieces);

                $args = $args ? explode(',', $args) : [];
                $builder = $rule ? call_user_func_array([$builder, $rule], $args) : $builder;
            }
        } while (($context = array_shift($contexts)) && ($builder = $builder->or()));

        return $builder;
    }

    public function assocRules(array $rules): static
    {
        if (!is_array($this->value)) {
            throw new UnexpectedValueException("Cannot apply assoc rules to a value that is not an array.");
        }

        foreach ($rules as $attr => $rules) {
            $sensitive = 0;
            $attr = preg_replace('/^#/', '', $attr, 1, $sensitive);

            $currValue = ArrayUtil::get($attr, $this->value);

            $builder = $this->applyAssocRule($attr, $currValue, $rules, (bool)$sensitive);
            $changedValue = $builder->validate()->getOriginal();

            if ($changedValue !== $currValue) {
                ArrayUtil::set($attr, $this->value, $changedValue);
            }
        }

        return $this;
    }

    protected function applyAssocRule(string $attr, $value, array|string $rules, bool $sensitive = false): static
    {
        $builder = $this->clone($value, $this->parent, $attr, (bool)$sensitive);

        if (!is_array($rules)) {
            $rules = [strval($rules)];
        }

        do {
            $rule = array_shift($rules);
            $builder = $builder->rules($rule);
        } while ($rules && ($builder = $builder->or()));

        return $builder;
    }
}
