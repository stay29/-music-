<?php
/**
 * 教室
 * User: antony
 * Date: 2019/7/16
 */
namespace app\index\model;
use function app\index\controller\_where;
use think\Model;
use think\Db;


class Classroom extends BaseModel
{
    protected $pk = 'room_id';
    protected $deleteTime = 'delete_time';
    protected $table = 'erp2_classrooms';
    protected $field = true;
    protected $autoWriteTimestamp = true;
    protected $update = [];

    public function searchRoomNameAttr($query, $value, $data)
    {
        $query->where('room_name','like', $value . '%');
    }

    public function searchStatusAttr($query, $value, $data)
    {
        $query->where('status', $value);
    }

    public function searchOrIdAttr($query, $value, $data)
    {
        $query->where('or_id', $value);
    }
}