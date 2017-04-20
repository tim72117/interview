<?php

namespace Plat\Interview;

use Input;
use Cdb;
use DB;

class InforRepository
{
    public static function instance()
    {
        return new self();
    }

    public function simple_infor()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        if (array_key_exists("simple_rel", $key)) {
            $simple_rel = \DB::table('interview_answers')->where('id', '=', $key['simple_rel']['answer_id'])->where('value', '=', $key['simple_rel']['value'])->first();
        }
        if (array_key_exists("simple_name", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'), 'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'), 'ques' => 3, 'pay' =>0
                                            ],
                                            [
                                                'name'    =>  (!isset($simple_rel) || $key['simple_rel']['value'] == 1 ? ' ' : '['.$simple_rel->title.'] ').$key['simple_name']['value']
                                            ]);
        }
        if (array_key_exists("simple_phone", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 3,
                                                'pay' =>0
                                            ],
                                            [
                                                'phone'   =>  $key['simple_phone']['value']
                                            ]);
        }
        if (array_key_exists("simple_tel", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 3,
                                                'pay' =>0
                                            ],
                                            [
                                                'tel'     =>  $key['simple_tel']['value']
                                            ]);
        }
        if (array_key_exists("simple_work_tel", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 3,
                                                'pay' =>0
                                            ],
                                            [
                                                'work_tel'=>  $key['simple_work_tel']['value']
                                            ]);
        }
        if (array_key_exists("simple_email", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 3,
                                                'pay' =>0],
                                            [
                                                'email'   =>  $key['simple_email']['value']
                                            ]);
        }
        if (array_key_exists("simple_address", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 3,
                                                'pay' =>0],
                                            [
                                                'address' =>  $key['simple_address']['value']
                                            ]);
        }

        if (array_key_exists("simple_other_contact", $key) || array_key_exists("simple_contact_time_moring", $key) || array_key_exists("simple_contact_time_afternoon", $key) || array_key_exists("simple_contact_time_night", $key)) {
            Cdb\Baby_family::updateOrCreate([

                                            'visit_id' => Input::get('interview.visit_id'), 'record_id' => Input::get('interview.id'),
                                            'month' => Input::get('book.wave.month'),
                                            'baby_id' => Input::get('baby.id'),
                                            'ques' => 3, 'pay' =>0],
                                            [
                                            'remark' => (isset($key['simple_other_contact']['value']) ? '其他聯絡方式:'.$key['simple_other_contact']['value'] : '').(isset($key['simple_contact_time_moring']['ques_title']) ||
                                                         isset($key['simple_contact_time_afternoon']['ques_title']) ||
                                                         isset($key['simple_contact_time_night']['ques_title'])           ? ' 方便電聯時間:'
                                                            .(isset($key['simple_contact_time_moring']['ques_title'])     ? ' '.substr($key['simple_contact_time_moring']['ques_title'], 0, 6) : '')
                                                            .(isset($key['simple_contact_time_afternoon']['ques_title'])  ? ' '.substr($key['simple_contact_time_afternoon']['ques_title'], 0, 6) : '')
                                                            .(isset($key['simple_contact_time_night']['ques_title'])      ? ' '.substr($key['simple_contact_time_night']['ques_title'], 0, 6) : '') : '')
                                            ]);
        }

        if (array_key_exists("parent", $key)) {
            Cdb\Visit_parent::updateOrCreate([
                                                'id' => Input::get('interview.visit_id')
                                            ],
                                            [
                                                'wave_id' => Input::get('book.wave.id'),
                                                'reason' => '成功', 'result' => 0
                                            ]);
            Cdb\Baby::updateOrCreate(['id' => Input::get('baby.id')], ['warn' => 0]);
        }

        if (array_key_exists("parent_agree", $key)) {
            if ($key['parent_agree']['value'] == 3) {
                Cdb\Baby_family::Create([
                                            'baby_id' => Input::get('baby.id'),
                                            'ques' => 2,
                                            'visit_id' => Input::get('interview.visit_id'),
                                            'record_id' => Input::get('interview.id'),
                                            'pay' =>1,
                                            'month' => Input::get('book.wave.month')]
                                        );
                if (array_key_exists("parent_rel", $key)) {
                    $parent_rel = \DB::table('interview_answers')->where('id', '=', $key['parent_rel']['answer_id'])->where('value', '=', $key['parent_rel']['value'])->first();
                }
                if (array_key_exists("parent_name", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 2], ['name'    =>  '['.$parent_rel->title.']'.$key['parent_name']['value']]);
                }
                if (array_key_exists("parent_phone", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 2], ['phone'   =>  $key['parent_phone']['value']]);
                }
                if (array_key_exists("parent_tel", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 2], ['tel'     =>  $key['parent_tel']['value']]);
                }
                if (array_key_exists("parent_work_tel", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 2], ['work_tel'=>  $key['parent_work_tel']['value']]);
                }
                if (array_key_exists("parent_email", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 2], ['email'   =>  $key['parent_email']['value']]);
                }
                if (array_key_exists("parent_address", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 2], ['address' =>  $key['parent_address']['value']]);
                }
            }
        }
    }

    public function parent_infor()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        if (array_key_exists("parent_rel", $key)) {
            $parent_rel = \DB::table('interview_answers')->where('id', '=', $key['parent_rel']['answer_id'])->where('value', '=', $key['parent_rel']['value'])->first();
        }
        if (array_key_exists("parent_name", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 2,
                                                'pay' =>0
                                            ],
                                            [
                                                'name'    =>  (!isset($parent_rel) || $key['parent_rel']['value'] == 1 ? ' ' : '['.$parent_rel->title.'] ').$key['parent_name']['value']]
                                            );
        }
        if (array_key_exists("parent_phone", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 2,
                                                'pay' =>0
                                            ],
                                            [
                                                'phone'   =>  $key['parent_phone']['value']
                                            ]);
        }
        if (array_key_exists("parent_tel", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 2,
                                                'pay' =>0
                                            ],
                                            [
                                                'tel'     =>  $key['parent_tel']['value']
                                            ]);
        }
        if (array_key_exists("parent_work_tel", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 2,
                                                'pay' =>0
                                            ],
                                            [
                                                'work_tel'=>  $key['parent_work_tel']['value']
                                            ]);
        }
        if (array_key_exists("parent_email", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 2,
                                                'pay' =>0
                                            ],
                                            [
                                                'email'   =>  $key['parent_email']['value']
                                            ]);
        }
        if (array_key_exists("parent_address", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 2,
                                                'pay' =>0
                                            ],
                                            [
                                                'address' =>  $key['parent_address']['value']
                                            ]);

            $address = Cdb\Baby_address::Create([
                                                'baby_id' => Input::get('baby.id'),
                                                'wave_id' => Input::get('baby.wave.id'),
                                                'address' => $key['parent_address']['value']
                                                ]);

            Cdb\Baby::updateOrCreate(['id' => Input::get('baby.id')],['address_id' => $address->id]);}

        if (array_key_exists("parent_other_contact", $key) || array_key_exists("parent_contact_time_moring", $key) || array_key_exists("parent_contact_time_afternoon", $key) || array_key_exists("parent_contact_time_night", $key)) {
            Cdb\Baby_family::updateOrCreate([
                                                'visit_id' => Input::get('interview.visit_id'),
                                                'record_id' => Input::get('interview.id'),
                                                'month' => Input::get('book.wave.month'),
                                                'baby_id' => Input::get('baby.id'),
                                                'ques' => 2,
                                                'pay' =>0],
                                            [
                                                'remark' => (isset($key['parent_other_contact']['value']) ? '其他聯絡方式:'.$key['parent_other_contact']['value'] : '').
                                                        (
                                                            isset($key['parent_contact_time_moring']['ques_title']) ||
                                                            isset($key['parent_contact_time_afternoon']['ques_title']) ||
                                                            isset($key['parent_contact_time_night']['ques_title']
                                                        ) ? ' 方便電聯時間:'. (isset($key['parent_contact_time_moring']['ques_title'])    ? ' ' .substr($key['parent_contact_time_moring']['ques_title'], 0, 6) : '')
                                                                            . (isset($key['parent_contact_time_afternoon']['ques_title']) ? ' ' .substr($key['parent_contact_time_afternoon']['ques_title'], 0, 6) : '')
                                                                            . (isset($key['parent_contact_time_night']['ques_title'])     ? ' ' .substr($key['parent_contact_time_night']['ques_title'], 0, 6) : '') : '')
                                            ]);
        }

        if (array_key_exists("simple_agree", $key)) {
            if ($key['simple_agree']['value'] == 2) {

                Cdb\Agree::Create(['baby_id' => Input::get('baby.id'), 'ques' => 3, 'month' => Input::get('book.wave.month'), 'visit_id' => Input::get('interview.visit_id')]);

            } elseif ($key['simple_agree']['value'] == 3) {
                Cdb\Baby_family::Create([
                                            'baby_id' => Input::get('baby.id'), 'ques' => 3, 'visit_id' => Input::get('interview.visit_id'),
                                            'record_id' => Input::get('interview.id'), 'pay' =>1, 'month' => Input::get('book.wave.month')
                                        ]);

                if (array_key_exists("simple_rel", $key)) {
                    $simple_rel = \DB::table('interview_answers')->where('id', '=', $key['simple_rel']['answer_id'])->where('value', '=', $key['simple_rel']['value'])->first();
                }
                if (array_key_exists("simple_name", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 3], ['name'    =>  '['.$simple_rel->title.'] '.$key['simple_name']['value']]);
                }
                if (array_key_exists("simple_phone", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 3], ['phone'   =>  $key['simple_phone']['value']]);
                }
                if (array_key_exists("simple_tel", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 3], ['tel'     =>  $key['simple_tel']['value']]);
                }
                if (array_key_exists("simple_work_tel", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 3], ['work_tel'=>  $key['simple_work_tel']['value']]);
                }
                if (array_key_exists("simple_email", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 3], ['email'   =>  $key['simple_email']['value']]);
                }
                if (array_key_exists("simple_address", $key)) {
                    Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'baby_id' => Input::get('baby.id'), 'ques' => 3], ['address' =>  $key['simple_address']['value']]);
                }
            }
        }
        if (array_key_exists("test", $key)) {
            Cdb\Baby_family::updateOrCreate(['visit_id' => Input::get('interview.visit_id'), 'record_id' => Input::get('interview.id'), 'month' => Input::get('book.wave.month'),  'baby_id' => Input::get('baby.id'), 'ques' => 2, 'pay' =>0], ['test' => $key['test']['value']]);
            Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '成功', 'result' => 0]);
            Cdb\Baby::updateOrCreate(['id' => Input::get('baby.id')], ['warn' => 0]);
        }
    }

    public function nanny_infor()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '成功', 'result' => 0, 'nanny_id' => Input::get('nanny.id')]);
        if (array_key_exists("nanny_email", $key)) {
            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['email'   =>  $key['nanny_email']['value']]);
        }
        if (array_key_exists("nanny_phone", $key)) {
            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['phone'   =>  $key['nanny_phone']['value']]);
        }
        if (array_key_exists("nanny_tel", $key)) {
            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['tel'     =>  $key['nanny_tel']['value']]);
        }
        if (array_key_exists("nanny_work_tel", $key)) {
            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['work_tel'=>  $key['nanny_work_tel']['value']]);
        }
        if (array_key_exists("nanny_address", $key)) {
            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['address' =>  $key['nanny_address']['value']]);
        }
        if (array_key_exists("nanny_other_contact", $key) || array_key_exists("nanny_contact_time_moring", $key) || array_key_exists("nanny_contact_time_afternoon", $key) || array_key_exists("nanny_contact_time_night", $key)) {
            Cdb\Nanny::updateOrCreate([
                                        'id' => Input::get('nanny.id')],
                                        ['remark' => (isset($key['nanny_other_contact']['value']) ? '其他聯絡方式:'.$key['nanny_other_contact']['value'] : '') .
                                            (
                                                isset($key['nanny_contact_time_moring']['ques_title']) || isset($key['nanny_contact_time_afternoon']['ques_title']) || isset($key['nanny_contact_time_night']['ques_title']) ?
                                                        ' 方便電聯時間:'. (isset($key['nanny_contact_time_moring']['ques_title'])    ? ' ' .substr($key['nanny_contact_time_moring']['ques_title'], 0, 6) : '')
                                                                        . (isset($key['nanny_contact_time_afternoon']['ques_title']) ? ' ' .substr($key['nanny_contact_time_afternoon']['ques_title'], 0, 6) : '')
                                                                        . (isset($key['nanny_contact_time_night']['ques_title'])     ? ' ' .substr($key['nanny_contact_time_night']['ques_title'], 0, 6) : '') : ''
                                            )
                                        ]);
        }

        if (array_key_exists("test", $key)) {

            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['test' => $key['test']['value']]);

            Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '成功', 'result' => 0, 'nanny_id' => Input::get('nanny.id')]);

            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['result' =>0, 'warn' => 0]);
        }
    }

    public function fixInfor()
    {
        if (Input::get('ques') == 2 || Input::get('ques') == 3) {
            Cdb\Baby_family::updateOrCreate([
                                                'id' => Input::get('family.id')
                                            ],
                                            [   'name' => Input::get('family.name'),
                                                'tel' =>Input::get('family.tel'),
                                                'phone' =>Input::get('family.phone'),
                                                'work_tel' => Input::get('family.work_tel'),
                                                'email' => Input::get('family.email'), 'address' => Input::get('family.address'),
                                                'remark' => Input::get('family.remark')
                                            ]);

        } elseif (Input::get('ques') == 4) {

            $pay = Input::get('family.tel')== null && Input::get('family.work_tel')== null && Input::get('family.phone')== null && Input::get('family.address')== null ? 0 : 1;

        if (Input::get('family.change') == null) {

            Cdb\Nanny::updateOrCreate([
                                        'id' => Input::get('family.id')
                                      ],
                                      [
                                        'name' => Input::get('family.name'),
                                        'tel' =>Input::get('family.tel'),
                                        'work_tel' => Input::get('family.work_tel'),
                                        'phone' =>Input::get('family.phone'),
                                        'address' => Input::get('family.address'),
                                        'change' => date("Y-m-d"),
                                        'pay' => $pay ==0 ? 0 : 1,
                                        'remark' => Input::get('family.remark')
                                      ]);

                return ['pay' => $pay ==0 ? 0 : 1,'time' => 60];

            } else {

            Cdb\Nanny::updateOrCreate([
                    'id' => Input::get('family.id')],
                            [
                                'name' => Input::get('family.name'),
                                'tel' =>Input::get('family.tel'),
                                'work_tel' => Input::get('family.work_tel'),
                                'phone' =>Input::get('family.phone'),
                                'address' => Input::get('family.address'),
                                'pay' => $pay ==0 ? 0 : 1,
                                'remark' => Input::get('family.remark'),
                            ]);

                return ['pay' => $pay ==0 ? 0 : 1, 'time' => Input::get('family.time')];
            }

        } elseif (Input::get('ques') == 5) {

            $pay = Input::get('family.tel')== null && Input::get('family.work_tel')== null && Input::get('family.phone')== null && Input::get('family.address')== null && Input::get('family.school_name')== null ? 0 : 1;

            if (Input::get('family.change') == null) {

                Cdb\Nanny::updateOrCreate([
                                            'id' => Input::get('family.id')
                                          ],
                                          [
                                            'name' => Input::get('family.name'),
                                            'tel' =>Input::get('family.tel'),
                                            'work_tel' => Input::get('family.work_tel'),
                                            'address' => Input::get('family.address'),
                                            'phone' =>Input::get('family.phone'),
                                            'school_name' => Input::get('family.school_name'),
                                            'class_name' => Input::get('family.class_name'),
                                            'change' => date("Y-m-d"),
                                            'pay' =>$pay ==0 ? 0 : 1 ,
                                            'remark' => Input::get('family.remark')
                                          ]);

                return ['pay' => $pay ==0 ? 0 : 1, 'time' => 60];

            } else {

                Cdb\Nanny::updateOrCreate([
                                            'id' => Input::get('family.id')], ['name' => Input::get('family.name'),
                                            'tel' =>Input::get('family.tel'),
                                            'work_tel' => Input::get('family.work_tel'),
                                            'address' => Input::get('family.address'),
                                            'phone' =>Input::get('family.phone'),
                                            'school_name' => Input::get('family.school_name'),
                                            'class_name' => Input::get('family.class_name'),
                                            'pay' =>$pay ==0 ? 0 : 1 , 'remark' => Input::get('family.remark')
                                          ]);

                return ['pay' => $pay ==0 ? 0 : 1, 'time' => Input::get('family.time')];
            }

        } elseif (Input::get('ques') == 0) {

            Cdb\Baby_contact::updateOrCreate([
                                                'id' => Input::get('family.id')
                                            ],
                                            [
                                                'name' => Input::get('family.name'),
                                                'tel' =>Input::get('family.tel'),
                                                'address' => Input::get('family.address'),
                                                'remark' => Input::get('family.remark')
                                            ]);
        }
        // else if (Input::get('ques') == 99) {
        //     $baby = Cdb\Baby::find(Input::get('family.id'));
        //     if ($baby->name != Input::get('family.name')) {
        //         Cdb\Baby::updateOrCreate(['id' => Input::get('family.id')], ['name' => Input::get('family.name')]);
        //     }
        //     else {
        //         $address = Cdb\Baby_address::Create(['baby_id' => Input::get('family.id'), 'wave_id' => Input::get('family.wave.id'), 'address' => Input::get('family.address')]);
        //         Cdb\Baby::updateOrCreate(['id' => Input::get('family.id')], ['address_id' => $address->id]);
        //     }

        // }
    }

    public function tel_simple()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['wave_id' => Input::get('book.wave.id'),
                                          'result' => array_key_exists("result", $key) ? $key['result']['value'] : null]);

        if (array_key_exists("reason", $key)) {
            if ($key['reason']['value'] == 1) {
                if (array_key_exists("reject", $key)) {
                    $answer = DB::table('interview_answers')->where('id', '=', $key['reject']['answer_id'])->where('value', '=', $key['reject']['value'])->first();
                    Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $answer->title]);
                }
            } elseif ($key['reason']['value'] == 2) {
                if (array_key_exists("languagen", $key)) {
                    $answer = DB::table('interview_answers')->where('id', '=', $key['languagen']['answer_id'])->where('value', '=', $key['languagen']['value'])->first();
                    Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $answer->title]);
                }
            }
        }
        if (array_key_exists("other", $key)) {
            Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $key['other']['value']]);
        }
        if (array_key_exists("simple_name", $key)  || array_key_exists("simple_tel", $key)     ||
                array_key_exists("simple_email", $key) || array_key_exists("simple_address", $key)
               ) {
            Cdb\Baby_family::Create(['baby_id'    => Input::get('baby.id'), 'visit_id'  => Input::get('interview.visit_id'),
                                          'record_id'  => Input::get('interview.id'),
                                          'ques'    => Input::get('book.wave.id') < 12 ?  '2' : '3',
                                          'pay' => 0, 'month' => Input::get('book.wave.month'),
                                          'name'    => array_key_exists("simple_name", $key)    ? $key['simple_name']['value']                  : null,
                                          'tel'     => array_key_exists("simple_tel", $key)     ? $key['simple_tel']['value']                   : null,
                                          'email'   => array_key_exists("simple_email", $key)   ? $key['simple_email']['value']                 : null,
                                          'address' => array_key_exists("simple_address", $key) ? $key['simple_address']['value']               : null,
                                          'remark'  => array_key_exists("simple_date", $key)    ? '下次約訪時間:'.$key['simple_date']['value']   : null,]);
        }
        if (array_key_exists("ok", $key)) {
            if ($key['ok']['value'] == 1) {
                Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['result' => $key['result']['value']]);
            } elseif ($key['ok']['value'] == 2) {
                Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '可以進行訪問', 'result' => 4]);
            }
        }
    }

    public function tel_nanny()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['wave_id' => Input::get('book.wave.id'), 'nanny_id' => Input::get('nanny.id'),
                                         'result' => array_key_exists("result", $key) ? $key['result']['value'] : null]);

        if (array_key_exists("reason", $key)) {
            if ($key['reason']['value'] == 1) {
                if (array_key_exists("reject", $key)) {
                    $answer = DB::table('interview_answers')->where('id', '=', $key['reject']['answer_id'])->where('value', '=', $key['reject']['value'])->first();
                    Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $answer->title]);
                }
            } elseif ($key['reason']['value'] == 2) {
                if (array_key_exists("languagen", $key)) {
                    $answer = DB::table('interview_answers')->where('id', '=', $key['languagen']['answer_id'])->where('value', '=', $key['languagen']['value'])->first();
                    Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $answer->title]);
                }
            }
        }
        if (array_key_exists("other", $key)) {
            Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $key['other']['value']]);
        }
        if (array_key_exists("ok", $key)) {
            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['warn2' => 0]);
            if ($key['ok']['value'] == 1 && $key['result']['value'] == 1) {
                Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '不能進行訪問', 'result' => $key['result']['value']]);
                Cdb\Nanny::find(Input::get('nanny.id'))->delete();
            } elseif ($key['ok']['value'] == 1 && $key['result']['value'] != 1) {
                Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['result' => $key['result']['value']]);
            } elseif ($key['ok']['value'] == 2) {
                Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '可以進行訪問', 'result' => 4]);
            }
        }
        if (array_key_exists("choose", $key)) {
            if ($key['choose']['value'] == 1) {
                if (array_key_exists("nanny_name", $key)) {
                    Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['name'    =>  $key['nanny_name']['value']]);
                }
                if (array_key_exists("nanny_address", $key)) {
                    Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['address' =>  $key['nanny_address']['value']]);
                }
                if (array_key_exists("nanny_tel", $key)) {
                    Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['tel'     =>  $key['nanny_tel']['value']]);
                }
                if (array_key_exists("nanny_email", $key)) {
                    Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['email'   =>  $key['nanny_email']['value']]);
                }
                Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '選擇面訪', 'result' => 4]);
            } elseif ($key['choose']['value'] == 2) {
                if (array_key_exists("nanny_name", $key)) {
                    Cdb\Nanny::updateOrCreate(['id'   => Input::get('nanny.id')], ['name'    =>  $key['nanny_name']['value']]);
                }
                if (array_key_exists("nanny_tel", $key)) {
                    Cdb\Nanny::updateOrCreate(['id'   => Input::get('nanny.id')], ['tel'     =>  $key['nanny_tel']['value']]);
                }
                if (array_key_exists("nanny_email", $key)) {
                    Cdb\Nanny::updateOrCreate(['id'   => Input::get('nanny.id')], ['email'   =>  $key['nanny_email']['value']]);
                }
                Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '選擇網路訪', 'result' => 4]);
            }
        }
    }

    public function ques_parent()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('record.id'));

        if (array_key_exists("nanny_agree", $key)) {
            if ($key['nanny_agree']['value'] == 2) {
                if (array_key_exists("ques_infor", $key) && $key['ques_infor']['value'] == 1) {
                    if (array_key_exists("reason", $key)) {
                        $answer = DB::table('interview_answers')->where('id', '=', $key['reason']['answer_id'])->where('value', '=', $key['reason']['value'])->first();
                        Cdb\Nanny::Create(['baby_id' => Input::get('baby.id'), 'ques' => 4, 'visit_id' => Input::get('record.visit_id'),
                                            'agree' => 1, 'pay' =>0, 'result' =>0, 'status' => $answer->title, 'month' => Input::get('baby.wave.month')]);
                    }
                }
                if (array_key_exists("ques_infor", $key) && $key['ques_infor']['value'] == 2) {
                    if (array_key_exists("reason", $key)) {
                        $answer = DB::table('interview_answers')->where('id', '=', $key['reason']['answer_id'])->where('value', '=', $key['reason']['value'])->first();
                        Cdb\Nanny::Create(['baby_id' => Input::get('baby.id'), 'ques' => 5, 'visit_id' => Input::get('record.visit_id'),
                                            'agree' => 1, 'pay' =>0, 'result' =>0, 'status' => $answer->title, 'month' => Input::get('baby.wave.month')]);
                    }
                }
            } elseif ($key['nanny_agree']['value'] == 3) {
                if (array_key_exists("ques_infor", $key) && $key['ques_infor']['value'] == 1) {
                    $visit = Cdb\Nanny::Create(['baby_id' => Input::get('baby.id'), 'ques' => 4, 'visit_id' => Input::get('record.visit_id'),
                                            'agree' => 0, 'pay' =>1, 'result' =>0, 'month' => Input::get('baby.wave.month')]);
                    if (array_key_exists("nanny_name", $key)  || array_key_exists("nanny_phone", $phone)  ||
                       array_key_exists("nanny_tel", $key)   || array_key_exists("nanny_work_tel", $key) ||
                       array_key_exists("nanny_email", $key) || array_key_exists("nanny_address", $key)
                       ) {
                        Cdb\Nanny::updateOrCreate(['id' => $visit->id, 'pay' =>1],
                                                        ['name'     => array_key_exists("nanny_name", $key) ? $key['nanny_name']['value']     : null,
                                                         'phone'    => array_key_exists("nanny_phone", $key) ? $key['nanny_phone']['value']    : null,
                                                         'tel'      => array_key_exists("nanny_tel", $key) ? $key['nanny_tel']['value']      : null,
                                                         'work_tel' => array_key_exists("nanny_work_tel", $key) ? $key['nanny_work_tel']['value'] : null,
                                                         'email'    => array_key_exists("nanny_email", $key) ? $key['nanny_email']['value']    : null,
                                                         'address'  => array_key_exists("nanny_address", $key) ? $key['nanny_address']['value']  : null,]);
                    }
                }
                if (array_key_exists("ques_infor", $key) && $key['ques_infor']['value'] == 2) {
                    $visit = Cdb\Nanny::Create(['baby_id' => Input::get('baby.id'), 'ques' => 5, 'visit_id' => Input::get('record.visit_id'),
                                            'agree' => 0, 'pay' =>1, 'result' =>0, 'month' => Input::get('baby.wave.month')]);
                    if (array_key_exists("school_name", $key)   || array_key_exists("school_phone", $key)  ||
                       array_key_exists("school_tel", $key)    || array_key_exists("school_work_tel", $key) ||
                       array_key_exists("school_email", $key)  || array_key_exists("school_address", $key)  ||
                       array_key_exists("school_school", $key) || array_key_exists("school_class", $key)
                       ) {
                        Cdb\Nanny::updateOrCreate(['id'     => $visit->id, 'pay' =>1],
                                                        ['name'         => array_key_exists("school_name", $key) ? $key['school_name']['value']     : null,
                                                         'phone'        => array_key_exists("school_phone", $key) ? $key['school_phone']['value']    : null,
                                                         'tel'          => array_key_exists("school_tel", $key) ? $key['school_tel']['value']      : null,
                                                         'work_tel'     => array_key_exists("school_work_tel", $key) ? $key['school_work_tel']['value'] : null,
                                                         'email'        => array_key_exists("school_email", $key) ? $key['school_email']['value']    : null,
                                                         'address'      => array_key_exists("school_address", $key) ? $key['school_address']['value']  : null,
                                                         'school_name'  => array_key_exists("school_school", $key) ? $key['school_school']['value']   : null,
                                                         'class_name'   => array_key_exists("school_class", $key) ? $key['school_class']['value']    : null,]);
                    }
                }
            }
        }
    }

    public function parent_stop()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        if (array_key_exists("reason", $key)) {
            if (array_key_exists("other", $key)) {
                Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $key['other']['value']]);
            } else {
                $answer = \DB::table('interview_answers')->where('id', '=', $key['reason']['answer_id'])->where('value', '=', $key['reason']['value'])->first();
                Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $answer->title.'(中斷)']);
            }
        }
        if (array_key_exists("result", $key)) {
            Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['wave_id' =>  Input::get('book.wave_id'), 'result' => $key['result']['value']]);
            Cdb\Baby::updateOrCreate(['id' => Input::get('baby.id')], ['warn' => 0]);
            if ($key['result']['value'] ==1 && array_key_exists("deleted", $key)) {
                if ($key['deleted']['value'] == 1 && (Input::get('baby.simple_wave') == null || Input::get('baby.simple') != null) && !(!Input::get('baby.school') || !Input::get('baby.nanny'))) {
                    Cdb\Baby::find(Input::get('baby.id'))->delete();
                }
            }
        }
    }

    public function nanny_stop()
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('interview.id'));

        if (array_key_exists("reason", $key)) {
            $answer = \DB::table('interview_answers')->where('id', '=', $key['reason']['answer_id'])->where('value', '=', $key['reason']['value'])->first();
            Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $answer->title.'(中斷)']);
        }
        if (array_key_exists("other", $key)) {
            Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => $key['other']['value'].'(中斷)']);
        }
        if (array_key_exists("result", $key)) {
            Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['result' => $key['result']['value'], 'nanny_id' => Input::get('nanny.id')]);
            Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['warn' => 0], ['remark' => array_key_exists("nanny_date", $key) ? '下次約訪時間:'.$key['nanny_date']['value']  : null]);
        }
    }

    public function updateOrCreateInfoFile()
    {
        if (Input::get('add.ques') == 2 || Input::get('add.ques') == 3) {
            $month = Input::get('add.ques') == 2 ? Input::get('baby.wave.month') : Input::get('baby.simple_wave.month');
            if (Input::get('agree') != null) {
                Cdb\Baby_family::updateOrCreate(
                        [
                            'baby_id'  => Input::get('agree.baby_id'),
                            'visit_id' => Input::get('agree.visit_id')
                        ],
                        [
                            'pay'      => 1
                        ]);
                        $add = Cdb\Baby_family::Create(
                        [
                            'baby_id'   => Input::get('agree.baby_id'),
                            'ques'      => Input::get('add.ques'),
                            'visit_id'  => Input::get('agree.visit_id'),
                            'record_id' => 0,
                            'pay'       => 0,
                            'name'      => Input::get('add.name'),
                            'tel'       => Input::get('add.tel'),
                            'work_tel'  => Input::get('add.work_tel'),
                            'phone'     => Input::get('add.phone'),
                            'email'     => Input::get('add.email'),
                            'address'   => Input::get('add.address'),
                            'month'     => $month
                        ]);
            } else {
                $add = Cdb\Baby_family::Create(
                        [
                            'baby_id'   => Input::get('baby.id'),
                            'visit_id'  => 0,
                            'ques'      => Input::get('add.ques'),
                            'record_id' => 0,
                            'pay'       => 0,
                            'name'      => Input::get('add.name'),
                            'tel'       => Input::get('add.tel'),
                            'work_tel'  => Input::get('add.work_tel'),
                            'phone'     => Input::get('add.phone'),
                            'email'     => Input::get('add.email'),
                            'address'   => Input::get('add.address'),
                            'month'     => $month
                        ]);
            }
            return ['status' => Input::get('add.ques'), 'add' => $add];
        } else {
            $add = Cdb\Baby_contact::Create(
                        [
                            'baby_id'   => Input::get('baby.id'),
                            'wave_id'   => Input::get('baby.wave.id'),
                            'visit_id'  => 0,
                            'record_id' => 0,
                            'pay'       => 0,
                            'name'      => Input::get('add.name'),
                            'tel'       => Input::get('add.tel'),
                            'email'     => Input::get('add.email'),
                            'address'   => Input::get('add.address')
                        ]);

            if (Input::get('add.address') != null) {
                $address = Cdb\Baby_address::Create(
                    [
                            'baby_id' => Input::get('baby.id'),
                            'wave_id' => Input::get('baby.wave.id'),
                            'address' => Input::get('add.address')
                    ]);
            }
            return ['status' => 0, 'add' => $add];
        }
    }

    public function parent_watch()
    {
        Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '完整結束', 'result' => 5]);
    }

    public function nanny_watch()
    {
        Cdb\Visit_parent::updateOrCreate(['id' => Input::get('interview.visit_id')], ['reason' => '完整結束', 'result' => 5, 'nanny_id' => Input::get('nanny.id')]);
        Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['result' =>1, 'warn' => 0]);
        //Cdb\Nanny::find(Input::get('nanny.id'))->delete();
    }

}