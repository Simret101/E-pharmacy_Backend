<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'query',
        'response',
        'drug_name',
        'similarity_score',
        'is_pregnancy_query'
    ];

    protected $casts = [
        'is_pregnancy_query' => 'boolean',
        'similarity_score' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted created_at date
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    /**
     * Scope query to filter by drug name
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $drugName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDrugName($query, $drugName)
    {
        return $query->where('drug_name', $drugName);
    }

    /**
     * Scope query to filter by pregnancy queries
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $isPregnancy
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePregnancyQueries($query, $isPregnancy = true)
    {
        return $query->where('is_pregnancy_query', $isPregnancy);
    }

    /**
     * Scope query to filter by similarity score
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $minScore
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySimilarityScore($query, $minScore)
    {
        return $query->where('similarity_score', '>=', $minScore);
    }
}
