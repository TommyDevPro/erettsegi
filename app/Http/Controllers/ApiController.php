<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiController extends Controller
{
    private $BasePoints = 0;
    private $examPoints = 0;
    private $extraPoints = 0;
    private $dataRules = [
        'ELTE' => [
            'kotelezo' => 'matematika',
            'kotelezoen-valaszthato' => ['biológia', 'fizika', 'informatika', 'kémia']
        ],
        'PPKE' => [
            'kotelezo' => 'angol',
            'kotelezoen-valaszthato' => ['francia', 'német', 'olasz', 'orosz', 'spanyol', 'spanyol', 'történelem']
        ]
    ];
    // output: 476 (376 alappont + 100 többletpont)
    private $exampleData = [
        'valasztott-szak' => [
            'egyetem' => 'ELTE',
            'kar' => 'IK',
            'szak' => 'Programtervező informatikus',
        ],
        'erettsegi-eredmenyek' => [
            [
                'nev' => 'magyar nyelv és irodalom',
                'tipus' => 'közép',
                'eredmeny' => '70%',
            ],
            [
                'nev' => 'történelem',
                'tipus' => 'közép',
                'eredmeny' => '80%',
            ],
            [
                'nev' => 'matematika',
                'tipus' => 'emelt',
                'eredmeny' => '90%',
            ],
            [
                'nev' => 'angol nyelv',
                'tipus' => 'közép',
                'eredmeny' => '94%',
            ],
            [
                'nev' => 'informatika',
                'tipus' => 'közép',
                'eredmeny' => '95%',
            ],
            [
                'nev' => 'fizika',
                'tipus' => 'közép',
                'eredmeny' => '98%',
            ],
        ],
        'tobbletpontok' => [
            [
                'kategoria' => 'Nyelvvizsga',
                'tipus' => 'B2',
                'nyelv' => 'angol',
            ],
            [
                'kategoria' => 'Nyelvvizsga',
                'tipus' => 'C1',
                'nyelv' => 'német',
            ],
        ],
    ];
    // output: hiba, nem lehetséges a pontszámítás a kötelező érettségi tárgyak hiánya miatt
    private $exampleData2 = [
        'valasztott-szak' => [
            'egyetem' => 'ELTE',
            'kar' => 'IK',
            'szak' => 'Programtervező informatikus',
        ],
        'erettsegi-eredmenyek' => [
            [
                'nev' => 'matematika',
                'tipus' => 'emelt',
                'eredmeny' => '90%',
            ],
            [
                'nev' => 'angol nyelv',
                'tipus' => 'közép',
                'eredmeny' => '94%',
            ],
            [
                'nev' => 'informatika',
                'tipus' => 'közép',
                'eredmeny' => '95%',
            ],
        ],
        'tobbletpontok' => [
            [
                'kategoria' => 'Nyelvvizsga',
                'tipus' => 'B2',
                'nyelv' => 'angol',
            ],
            [
                'kategoria' => 'Nyelvvizsga',
                'tipus' => 'C1',
                'nyelv' => 'német',
            ],
        ],
    ];
    // output: hiba, nem lehetséges a pontszámítás a magyar nyelv és irodalom tárgyból elért 20% alatti eredmény miatt
    private $exampleData3 = [
        'valasztott-szak' => [
            'egyetem' => 'ELTE',
            'kar' => 'IK',
            'szak' => 'Programtervező informatikus',
        ],
        'erettsegi-eredmenyek' => [
            [
                'nev' => 'magyar nyelv és irodalom',
                'tipus' => 'közép',
                'eredmeny' => '15%',
            ],
            [
                'nev' => 'történelem',
                'tipus' => 'közép',
                'eredmeny' => '80%',
            ],
            [
                'nev' => 'matematika',
                'tipus' => 'emelt',
                'eredmeny' => '90%',
            ],
            [
                'nev' => 'angol nyelv',
                'tipus' => 'közép',
                'eredmeny' => '94%',
            ],
            [
                'nev' => 'informatika',
                'tipus' => 'közép',
                'eredmeny' => '95%',
            ],
        ],
        'tobbletpontok' => [
            [
                'kategoria' => 'Nyelvvizsga',
                'tipus' => 'B2',
                'nyelv' => 'angol',
            ],
            [
                'kategoria' => 'Nyelvvizsga',
                'tipus' => 'C1',
                'nyelv' => 'német',
            ],
        ],
    ];
    // Kimeneti Segédlet:
    // output: hiba, nem lehetséges a pontszámítás a kötelező érettségi tárgyak hiánya
    // output: hiba, nem lehetséges a pontszámítás a magyar nyelv és irodalom tárgyból elért 20% alatti eredmény miatt
    // output: 476 (376 alappont + 100 többletpont)
    // output: 470 (370 alappont + 100 többletpont)
    private function ValidateLogic()
    {
        if (
            $this->exampleData['valasztott-szak'] ==    [
                'egyetem' => 'ELTE',
                'kar' => 'IK',
                'szak' => 'Programtervező informatikus',
            ]
            || $this->exampleData['valasztott-szak'] ==    [
                'egyetem' => 'PPKE',
                'kar' => 'BTK',
                'szak' => 'Anglisztika',
            ]
        ) {
            $validator = Validator::make(
                $this->exampleData,
                [  //valasztott-szak
                    "valasztott-szak"    => "required|array|min:3",
                    "valasztott-szak.egyetem"    => "required|string|min:2|in:ELTE,PPKE",
                    "valasztott-szak.kar"    => "required|string|min:2|in:IK,BTK",
                    "valasztott-szak.szak"    => "required|string|min:4|in:Anglisztika,Programtervező informatikus",
                    "erettsegi-eredmenyek"    =>       function ($attribute, $value, $fail) {
                        $failed = false;
                        $intersect = (array_intersect(['magyar nyelv és irodalom', 'történelem', 'matematika'], data_get($this->exampleData, "erettsegi-eredmenyek.*.nev")));
                        if ($intersect !== ['magyar nyelv és irodalom', 'történelem', 'matematika']) {
                            $fail('Értékelni nem lehet  mert nincsenek megadva a kötelező érettségi tantárgyak : ' . data_get(array_diff(['magyar nyelv és irodalom', 'történelem', 'matematika'], $intersect), "*")[0]);
                        }
                    },
                    "erettsegi-eredmenyek.*.nev"    => "required|string|min:5",
                    "erettsegi-eredmenyek.*.tipus"    => "required|string|min:5|in:emelt,közép",
                    "erettsegi-eredmenyek.*.eredmeny"    => [
                        "required", "string", "min:2", function ($attribute, $value, $fail) {
                            $value = rtrim($value, '%');
                            if ($value <= 20) {
                                $fail('Értékelni nem lehet mert kevesebb mint 20 % az eredmenye az alábbi tantárgyból: ' .   data_get($this->exampleData, "erettsegi-eredmenyek." . explode(".", $attribute)[1] . ".nev"));
                            }
                            if ($value >= 100) {
                                $fail('Értékelni nem lehet mert több mint 100 % az eredmenye az alábbi tantárgyból: ' .   data_get($this->exampleData, "erettsegi-eredmenyek." . explode(".", $attribute)[1] . ".nev"));
                            }
                        }
                    ],
                    //tobbletpontok
                    "tobbletpontok"    => "array|min:0",
                    "tobbletpontok.*"    => "array|min:3",
                    "tobbletpontok.*.kategoria"    => "required|string|min:5",
                    "tobbletpontok.*.tipus"    => "required|string|min:2",
                    "tobbletpontok.*.nyelv"    => "required|string|min:2",
                ],
                [
                    "required" => " Struktúrális hiba hianyzó bemeneti elem:  :attribute ",
                    "valasztott-szak.egyetem"    => "required|string|min:2",
                    "valasztott-szak.kar"    => "required|string|min:2",
                    "valasztott-szak.szak"    => "required|string|min:4",
                    "erettsegi-eredmenyek.*"    => "required|array|min:3",
                    "erettsegi-eredmenyek.*.nev"    => "required|string|min:5",
                    "erettsegi-eredmenyek.*.tipus"    => "required|string|min:5|in:emelt,közép",
                    "tobbletpontok.*"    => "required|array|min:3",
                    "tobbletpontok.*.kategoria"    => "required|string|min:5",
                    "tobbletpontok.*.tipus"    => "required|string|min:2",
                    "tobbletpontok.*.nyelv"    => "required|string|min:2",
                ]
            );
            if (
                $validator->errors()->all() != null
            ) {
                dd(
                    $validator->errors()->all()
                );
            }
        }
    }
    private function getPrimarySubject($university)
    {
        return $this->dataRules[$university]["kotelezo"];
    }
    private function getSecondarySubject($university)
    {
        $subjects = $this->dataRules[$university]["kotelezoen-valaszthato"];
        $bestResult = 0;
        $bestSubject = "";
        foreach ($subjects as  $subject) {
            if ($bestResult < $this->getSubjectResult($subject)) {
                $bestSubject = $subject;
                $bestResult = $this->getSubjectResult($subject);
            }
        }
        return $bestSubject;
    }
    private function getSubjectResult($subject)
    {
        foreach ($this->exampleData['erettsegi-eredmenyek'] as $result) {
            if ($result["nev"] == $subject) {
                return rtrim($result["eredmeny"], "%");
            }
        }
    }
    private function BasePoints()
    {
        //Az alappontszám megállapításához csak a kötelező tárgy pontértékét és a legjobban
        // sikerült kötelezően választható tárgy pontértékét kell összeadni és az így kapott
        // összeget megduplázni.
        $kotelezoTargy = $this->getPrimarySubject($this->exampleData["valasztott-szak"]["egyetem"]);
        $kotelezoPont = $this->exampleData["erettsegi-eredmenyek"][array_search($kotelezoTargy, array_column($this->exampleData["erettsegi-eredmenyek"], 'nev'))]["eredmeny"];
        $kotelezoTipus = $this->exampleData["erettsegi-eredmenyek"][array_search($kotelezoTargy, array_column($this->exampleData["erettsegi-eredmenyek"], 'nev'))]["tipus"];
        if ($kotelezoTipus == "emelt") {
            $this->extraPoints = $this->extraPoints + 50;
        }
        $kotelezoenValaszthato = $this->getSecondarySubject($this->exampleData["valasztott-szak"]["egyetem"]);
        $kotelezoenValaszthatoPont = $this->exampleData["erettsegi-eredmenyek"][array_search($kotelezoenValaszthato, array_column($this->exampleData["erettsegi-eredmenyek"], 'nev'))]["eredmeny"];
        $kotelezoenValaszthatoTipus = $this->exampleData["erettsegi-eredmenyek"][array_search($kotelezoenValaszthato, array_column($this->exampleData["erettsegi-eredmenyek"], 'nev'))]["tipus"];
        if ($kotelezoenValaszthatoTipus == "emelt") {
            $this->extraPoints = $this->extraPoints + 50;
        }
        $this->BasePoints = (((int)rtrim($kotelezoPont, '%') + (int) rtrim($kotelezoenValaszthatoPont, '%')) * 2);
    }
    private function ExamPoints()
    {
        $nyelv = "";
        foreach ($this->exampleData["tobbletpontok"] as $vizsga) {
            if ($vizsga["kategoria"] == "Nyelvvizsga") {
                if ($vizsga['tipus'] == 'B2') {
                    $this->examPoints = $this->examPoints + 28;
                }
                if ($vizsga['tipus'] == 'C1') {
                    if ($nyelv == $vizsga["nyelv"]) {
                        $this->examPoints = 40;
                    } else {
                        $this->examPoints = $this->examPoints + 40;
                    }
                }
                $nyelv = $vizsga["nyelv"];
            }
        }
    }
    private function FeaturedPoints($subject)
    {
        foreach ($this->exampleData['erettsegi-eredmenyek'] as $result) {
            if ($result["nev"] == $subject) {
                dd($result["tipus"]);
            }
        }
    }
    public function index()
    {
        //Validáljuk a bemenetet
        $this->ValidateLogic();
        //Kiszámítjuk az eredményt, mert nem találtunk hibát előzőleg.
        $this->BasePoints();
        $this->ExamPoints();
        $bonusPoints = (int) $this->extraPoints + (int)  $this->examPoints;
        if ($bonusPoints > 100) $bonusPoints = 100;
        dd($this->BasePoints, $bonusPoints);
    }
}
