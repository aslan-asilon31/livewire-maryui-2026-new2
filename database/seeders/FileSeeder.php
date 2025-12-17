<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FileSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
            ['name' => '雇用契約書 (Koyou Keiyakusho) - Employment Contract', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '就業規則 (Shuugyou Kisoku) - Work Rules', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '給与明細 (Kyuuyo Meisai) - Payslip', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '人事評価 (Jinji Hyouka) - Performance Review', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '健康保険証 (Kenkou Hokenshou) - Health Insurance Card', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '年次休暇申請書 (Nenji Kyuuka Shinseisho) - Annual Leave Request', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '研修資料 (Kenshuu Shiryou) - Training Material', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '秘密保持契約書 (Himitsu Hojya Keiyakusho) - NDA', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => 'プロジェクト計画書 (Purojekuto Keikakusho) - Project Plan', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '出張申請書 (Shucchou Shinseisho) - Business Trip Request', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '請求書 (Seikyusho) - Invoice', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '経費精算書 (Keihi Seisansho) - Expense Report', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '会議議事録 (Kaigi Gijiroku) - Meeting Minutes', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => 'プロジェクト提案書 (Purojekuto Teiansho) - Project Proposal', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '業務マニュアル (Gyomu Manyuaru) - Operations Manual', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '安全衛生報告書 (Anzen Eisei Houkokusho) - Safety & Health Report', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '品質管理報告書 (Hinshitsu Kanri Houkokusho) - Quality Control Report', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '取締役会議事録 (Torishimariyaku Kaigi Gijiroku) - Board Meeting Minutes', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '社内通知 (Shanai Tsuuchi) - Internal Notice', 'type' => 'document', 'file_type' => 'pdf'],
            ['name' => '法務文書 (Houmu Bunsho) - Legal Document', 'type' => 'document', 'file_type' => 'pdf'],
        ];

        $userIds = User::pluck('id')->toArray(); // ambil semua ID user

        foreach ($files as $index => $file) {
            // ambil user random
            $randomUserId = $userIds[array_rand($userIds)];

            DB::table('files')->insert([
                'name' => $file['name'],
                'type' => $file['type'],
                'fileable_id' => $randomUserId,
                'fileable_type' => User::class,
                'file_type' => $file['file_type'],
                'is_activated' => 1,
                'queue_number' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
