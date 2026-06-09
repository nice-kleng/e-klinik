<?php

namespace App\Services;

use App\Models\Icd10Diagnosis;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class Icd10Service
{
    public function search(string $keyword): LengthAwarePaginator
    {
        return Icd10Diagnosis::where(function ($query) use ($keyword) {
            $query->where('code', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        })
            ->where('is_active', true)
            ->orderBy('code')
            ->paginate(20);
    }

    public function findByCode(string $code): ?Icd10Diagnosis
    {
        return Icd10Diagnosis::where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    public function getByCategory(string $category): Collection
    {
        return Icd10Diagnosis::where('category', $category)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
    }

    public function getCommonDiagnoses(int $limit = 50): Collection
    {
        return Icd10Diagnosis::where('is_active', true)
            ->orderBy('code')
            ->limit($limit)
            ->get();
    }

    public function validateCode(string $code): bool
    {
        return Icd10Diagnosis::where('code', $code)
            ->where('is_active', true)
            ->exists();
    }

    public function getCategoryList(): array
    {
        return Icd10Diagnosis::where('is_active', true)
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->mapWithKeys(function ($category) {
                $description = match ($category) {
                    'A00-B99' => 'Certain infectious and parasitic diseases',
                    'C00-D48' => 'Neoplasms',
                    'D50-D89' => 'Diseases of the blood and blood-forming organs',
                    'E00-E90' => 'Endocrine, nutritional and metabolic diseases',
                    'F00-F99' => 'Mental and behavioural disorders',
                    'G00-G99' => 'Diseases of the nervous system',
                    'H00-H59' => 'Diseases of the eye and adnexa',
                    'H60-H95' => 'Diseases of the ear and mastoid process',
                    'I00-I99' => 'Diseases of the circulatory system',
                    'J00-J99' => 'Diseases of the respiratory system',
                    'K00-K93' => 'Diseases of the digestive system',
                    'L00-L99' => 'Diseases of the skin and subcutaneous tissue',
                    'M00-M99' => 'Diseases of the musculoskeletal system',
                    'N00-N99' => 'Diseases of the genitourinary system',
                    'O00-O99' => 'Pregnancy, childbirth and the puerperium',
                    'P00-P96' => 'Certain conditions originating in the perinatal period',
                    'Q00-Q99' => 'Congenital malformations and chromosomal abnormalities',
                    'R00-R99' => 'Symptoms and signs not elsewhere classified',
                    'S00-T98' => 'Injury, poisoning and external causes',
                    'Z00-Z99' => 'Factors influencing health status and contact with health services',
                    default => $category,
                };
                return [$category => $description];
            })
            ->toArray();
    }
}
