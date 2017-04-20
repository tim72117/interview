<?php

namespace Plat\Interview;

use Cdb;

class SaveAsRepository
{
    public static function instance()
    {
        return new self();
    }

    public function key_save($record_id)
    {
        $key = [];
        Cdb\Visit_record::with(['ques_repository.save_as'])->where('id', '=', $record_id)->get()
                            ->map(function ($record) use (&$key) {
                                $record->ques_repository->map(function ($ques) use (&$key) {
                                    if ($ques->answer_id != null && $ques->save_as != null) {
                                        $key [$ques->save_as->key_name] = $ques->save_as->use == 'value' ? ['value' =>$ques->answer->is->value, 'answer_id' => $ques->answer->is->id]
                                                                                                         : ['value' =>$ques->string, 'ques_id' => null];
                                    }
                                });
                            });
        return $key;
    }
}