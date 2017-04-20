<?php

namespace Plat\Interview;

use Input;
use Cdb;
use DB;

class FailRepository
{
    public static function instance()
    {
        return new self();
    }

    public function noGet()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        if (array_key_exists("tel", $key) || array_key_exists("email", $key) || array_key_exists("address", $key)) {
            Cdb\Baby_contact::create([
                'baby_id' => Input::get('baby.id'),
                'visit_id' => Input::get('interview.visit_id'),
                'record_id' => Input::get('interview.id'),
                'tel' => array_key_exists("tel", $key) ? $key['tel']['value'] : null,
                'email' => array_key_exists("email", $key) ? $key['email']['value'] : null,
                'address' => array_key_exists("address", $key) ? $key['address']['value'] : null,
                'pay' => 1,
            ]);
        }

        if (array_key_exists("address", $key)) {
            $address = Cdb\Baby_address::create([
                'baby_id' => Input::get('baby.id'),
                'wave_id' => Input::get('baby.wave.id'),
                'address' => $key['address']['value'],
            ]);

            Cdb\Baby::updateOrCreate(['id' => Input::get('baby.id')], ['address_id' => $address->id]);
        }

        if (array_key_exists("reason", $key)) {
            if ($key['reason'] == 1) {
                array_key_exists("other", $key) && Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $other->string]);
            } else {
                $answer = \DB::table('interview_answers')->where('id', '=', $key['reason']['answer_id'])->where('value', '=', $key['reason']['value'])->first();
                Cdb\Visit_parent::updateOrCreate([
                    'id' => Input::get('interview.visit_id'),
                ], [
                    'reason' => $answer->title,
                ]);
            }
        }

        if (array_key_exists("other", $key)) {
            Cdb\Visit_parent::updateOrCreate([
                'id' => Input::get('interview.visit_id'),
            ], [
                'reason' => $key['other']['value'],
            ]);
        }

        if (array_key_exists("result", $key)) {
            Cdb\Baby::updateOrCreate([
                'id' => Input::get('baby.id'),
            ], [
                'warn' => 0,
            ]);

            if (Input::get('baby.visit') != null || Input::get('baby.now_wave') != 0) {
                Cdb\Visit_parent::updateOrCreate([
                    'id' => Input::get('interview.visit_id'),
                ], [
                    'wave_id' => Input::get('baby.simple_wave.id'),
                    'result' => $key['result']['value'],
                ]);
            } else {
                Cdb\Visit_parent::updateOrCreate([
                    'id' => Input::get('interview.visit_id'),
                ], [
                    'result' => $key['result']['value'],
                ]);
            }

            if ($key['result']['value'] ==1 && array_key_exists("who", $key)) {
                if ($key['who']['value'] == 1 && array_key_exists("deleted", $key)) {
                    if ($key['deleted']['value'] == 1 && (Input::get('baby.simple_wave') == null || Input::get('baby.simple') != null) && !(!Input::get('baby.school') || !Input::get('baby.nanny'))) {
                        Cdb\Baby::find(Input::get('baby.id'))->delete();
                    }
                } elseif ($key['who']['value'] == 2) {
                    Cdb\Visit_parent::updateOrCreate([
                        'id' => Input::get('interview.visit_id'),
                    ], [
                        'wave_id' => Input::get('baby.simple_wave.id'),
                    ]);
                }
            }
        }
    }

    public function get()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));
        // Cdb\Baby::updateOrCreate(['id' => Input::get('baby.id')], ['warn' => 0]);
        if (array_key_exists("tel", $key) || array_key_exists("email", $key)) {
            Cdb\Baby_contact::create([
                'baby_id' => Input::get('baby.id'),
                'visit_id' => Input::get('interview.visit_id'),
                'record_id' => Input::get('interview.id'),
                'tel' => array_key_exists("tel", $key) ? $key['tel']['value'] : null,
                'email' => array_key_exists("email", $key) ? $key['email']['value'] : null,
                'pay' =>1,
            ]);
        }

        if (array_key_exists("reason", $key)) {
            if ($key['reason']['value'] == 1) {
                if (array_key_exists("reject", $key)) {
                    $answer = DB::table('interview_answers')->where('id', '=', $key['reject']['answer_id'])->where('value', '=', $key['reject']['value'])->first();
                    Cdb\Visit_parent::updateOrCreate([
                        'id' => Input::get('interview.visit_id')
                    ], [
                        'reason' => $answer->title,
                    ]);
                }
            } elseif ($key['reason']['value'] == 2) {
                if (array_key_exists("languagen", $key)) {
                    $answer = DB::table('interview_answers')->where('id', '=', $key['languagen']['answer_id'])->where('value', '=', $key['languagen']['value'])->first();
                    Cdb\Visit_parent::updateOrCreate([
                        'id' => Input::get('interview.visit_id'),
                    ], [
                        'reason' => $answer->title,
                    ]);
                }
            } elseif ($key['reason']['value'] == 3) {
                $answer = DB::table('interview_answers')->where('id', '=', $key['reason']['answer_id'])->where('value', '=', $key['reason']['value'])->first();
                Cdb\Visit_parent::updateOrCreate([
                    'id' => Input::get('interview.visit_id'),
                ], [
                    'reason' => $answer->title,
                ]);
            } elseif (array_key_exists("other", $key)) {
                Cdb\Visit_parent::updateOrCreate([
                    'id' => Input::get('interview.visit_id'),
                ], [
                    'reason' => $key['other']['value'],
                ]);
            }
        }

        if (array_key_exists("result", $key)) {
            Cdb\Baby::updateOrCreate(['id' => Input::get('baby.id')], ['warn' => 0]);
            Cdb\Visit_parent::updateOrCreate([
                'id' => Input::get('interview.visit_id'),
            ], [
                'wave_id' => Input::get('interview.wave_id'),
                'result' => $key['result']['value'],
            ]);

            if ($key['result']['value'] ==1 && array_key_exists("who", $key)) {
                if ($key['who']['value'] == 1 && array_key_exists("deleted", $key)) {
                    if ($key['deleted']['value'] == 1 && (Input::get('baby.simple_wave') == null || Input::get('baby.simple') != null) && !(!Input::get('baby.school') || !Input::get('baby.nanny'))) {
                        Cdb\Baby::find(Input::get('baby.id'))->delete();
                    }
                } elseif ($key['who'] == 2) {
                    Cdb\Visit_parent::updateOrCreate([
                        'id' => Input::get('interview.visit_id'),
                    ], [
                        'wave_id' => Input::get('baby.simple_wave.id'),
                    ]);
                }
            }
        }
    }

    public function nanny_noGet()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        if (array_key_exists("reason", $key)) {
            $answer = DB::table('interview_answers')->where('id', '=', $key['reason']['answer_id'])->where('value', '=', $key['reason']['value'])->first();
            Cdb\Visit_parent::updateOrCreate([
                'id' => Input::get('interview.visit_id'),
            ], [
                'reason' => $answer->title,
            ]);
        }

        if (array_key_exists("other", $key)) {
            Cdb\Visit_parent::updateOrCreate([
                'id' => Input::get('interview.visit_id'),
            ], [
                'reason' => $key['other']['value'],
            ]);
        }

        if (array_key_exists("result", $key)) {
            Cdb\Nanny::updateOrCreate([
                'id' => Input::get('nanny.id'),
            ], [
                'warn' => 0,
            ]);

            Cdb\Visit_parent::updateOrCreate([
                'id' => Input::get('interview.visit_id'),
            ], [
                'result' => $key['result']['value'],
                'nanny_id' => Input::get('nanny.id'),
            ]);

            if ($key['result']['value'] == 1) {
                Cdb\Nanny::updateOrCreate([
                    'id' => Input::get('nanny.id'),
                ], [
                    'result' => 1,
                ]);

                Cdb\Nanny::find(Input::get('nanny.id'))->delete();
            }
        }
        // Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['warn' => 0]);
    }

    public function nanny_get()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        if (array_key_exists("reason", $key)) {
            if ($key['reason']['value'] == 1) {
                if (array_key_exists("reject", $key)) {
                    $answer = DB::table('interview_answers')->where('id', '=', $key['reject']['answer_id'])->where('value', '=', $key['reject']['value'])->first();
                    Cdb\Visit_parent::updateOrCreate([
                        'id' => Input::get('interview.visit_id'),
                    ], [
                        'reason' => $answer->title,
                    ]);
                }
            } elseif ($key['reason']['value'] == 2) {
                if (array_key_exists("languagen", $key)) {
                    $answer = DB::table('interview_answers')->where('id', '=', $key['languagen']['answer_id'])->where('value', '=', $key['languagen']['value'])->first();
                    Cdb\Visit_parent::updateOrCreate([
                        'id' => Input::get('interview.visit_id'),
                    ], [
                        'reason' => $answer->title,
                    ]);
                }
            } elseif ($key['reason']['value'] == 3) {
                if (array_key_exists("nanny_email", $key)) {
                    Cdb\Nanny::updateOrCreate([
                        'id' => Input::get('nanny.id'),
                    ], [
                        'email' => $key['nanny_email']['value'],
                    ]);
                }

                if (array_key_exists("nanny_phone", $key)) {
                    Cdb\Nanny::updateOrCreate([
                        'id' => Input::get('nanny.id'),
                    ], [
                        'phone' => $key['nanny_phone']['value'],
                    ]);
                }

                if (array_key_exists("nanny_tel", $key)) {
                    Cdb\Nanny::updateOrCreate([
                        'id' => Input::get('nanny.id'),
                    ], [
                        'tel' => $key['nanny_tel']['value'],
                    ]);
                }

                if (array_key_exists("nanny_work_tel", $key)) {
                    Cdb\Nanny::updateOrCreate([
                        'id' => Input::get('nanny.id'),
                    ], [
                        'work_tel' => $key['nanny_work_tel']['value'],
                    ]);
                }

                $answer = DB::table('interview_answers')->where('id', '=', $key['reason']['answer_id'])->where('value', '=', $key['reason']['value'])->first();
                Cdb\Visit_parent::updateOrCreate([
                    'id' => Input::get('interview.visit_id'),
                ], [
                    'reason' => $answer->title,
                ]);
            } elseif ($key['reason']['value'] == 4) {
                $answer = DB::table('interview_answers')->where('id', '=', $key['reason']['answer_id'])->where('value', '=', $key['reason']['value'])->first();
                Cdb\Visit_parent::updateOrCreate([
                    'id' => Input::get('interview.visit_id'),
                ], [
                    'reason' => $answer->title,
                ]);
            }
        }

        if (array_key_exists("other", $key)) {
            Cdb\Visit_parent::updateOrCreate([
                'id' => Input::get('interview.visit_id'),
            ], [
                'reason' => $key['other']['value'],
            ]);
        }

        if (array_key_exists("result", $key)) {
            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['warn' => 0], ['remark' => array_key_exists("nanny_date", $key) ? '下次約訪時間:'.$key['nanny_date']['value']  : null]);

            Cdb\Visit_parent::updateOrCreate([
                'id' => Input::get('interview.visit_id'),
            ], [
                'result' => $key['result']['value'],
                'nanny_id' => Input::get('nanny.id'),
            ]);

            if ($key['result']['value'] == 1 && $key['reason']['value'] != 3) {
                Cdb\Nanny::updateOrCreate([
                    'id' => Input::get('nanny.id'),
                ], [
                    'result' => 1,
                ]);

                Cdb\Nanny::find(Input::get('nanny.id'))->delete();
            }
        }
        // Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['warn' => 0]);
    }
}