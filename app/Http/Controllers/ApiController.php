<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiController extends Controller
{
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
                'eredmeny' => '22%',
            ],
            [
                'nev' => 'matematika',
                'tipus' => 'emelt',
                'eredmeny' => '54%',
            ],        [
                'nev' => 'történelem',
                'tipus' => 'emelt',
                'eredmeny' => '54%',
            ],        [
                'nev' => 'angol nyelv',
                'tipus' => 'közép',
                'eredmeny' => '44%',
            ],
            [
                'nev' => 'informatika',
                'tipus' => 'közép',
                'eredmeny' => '55%',
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
        $validator = Validator::make(
            $this->exampleData,
            [  //valasztott-szak
                "valasztott-szak"    => "required|array|min:3",
                "valasztott-szak.egyetem"    => "required|string|min:2",
                "valasztott-szak.kar"    => "required|string|min:2",
                "valasztott-szak.szak"    => "required|string|min:4",
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
    private function ComputeLogic()
    {
    }
    public function MainLogic()
    {
        //Validáljuk a bemenetet
        $this->ValidateLogic();
        //Kiszámítjuk az eredményt, mert nem találtunk hibát előzőleg.
        $this->ComputeLogic();
    }
}
