<?php
$conn = new mysqli('localhost', 'root', '', 'netcare');
$conn->set_charset('utf8mb4');

echo "===== DATA FETCHING TEST =====\n\n";

// Simulate form submission
$test_cases = [
    [
        'name' => 'Test 1: Create new entry (INSERT)',
        'data' => [
            'ajax' => '1',
            'id' => '',
            'pengajuan_id' => '103',
            'output' => '5',
            'utama' => '120',
            'tambahan' => '30',
            'jenis' => 'Disetujui',
            'keterangan' => 'Selesai dengan baik',
            'keterangan2' => 'Catatan tambahan'
        ]
    ],
    [
        'name' => 'Test 2: Verify INSERT success',
        'data' => [
            'query' => 'SELECT * FROM pengajuan_layanan WHERE pengajuan_id = 103 LIMIT 1'
        ]
    ],
    [
        'name' => 'Test 3: Update existing entry (UPDATE)',
        'data' => [
            'ajax' => '1',
            'id' => 'auto', // Will get from test 2
            'pengajuan_id' => '103',
            'output' => '10',
            'utama' => '200',
            'tambahan' => '50',
            'jenis' => 'Disetujui',
            'keterangan' => 'Updated keterangan',
            'keterangan2' => 'Updated catatan'
        ]
    ]
];

// Test 1: Check current data structure
$sql = "SELECT p.id as pengajuan_id, DATE_FORMAT(p.tanggal_pelaksanaan, '%Y-%m-%d') as tgl_pelaksanaan, p.judul, pl.id, pl.output, pl.utama, pl.tambahan, pl.jenis, pl.keterangan, pl.keterangan2 FROM pengajuan p LEFT JOIN pengajuan_layanan pl ON p.id = pl.pengajuan_id ORDER BY p.tanggal_pelaksanaan ASC LIMIT 1";

$result = $conn->query($sql);
if($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "1. Query hasil:\n";
    echo "   - pengajuan_id: " . $row['pengajuan_id'] . "\n";
    echo "   - tgl_pelaksanaan: " . $row['tgl_pelaksanaan'] . "\n";
    echo "   - judul: " . substr($row['judul'], 0, 60) . "\n";
    echo "   - pl.id (layanan): " . ($row['id'] ?? 'NULL - belum ada entry') . "\n";
    echo "   - output: " . ($row['output'] ?? 'NULL') . "\n\n";
}

// Check table structure
echo "2. Database Query untuk simpan:\n";
echo "   INSERT: INSERT INTO pengajuan_layanan (pengajuan_id, jenis, keterangan, output, utama, tambahan, keterangan2)\n";
echo "   UPDATE: UPDATE pengajuan_layanan SET jenis=?, keterangan=?, output=?, utama=?, tambahan=?, keterangan2=? WHERE id=?\n\n";

// Check if JavaScript receives correct data
echo "3. Data Attributes pada HTML:\n";
echo "   data-pengajuan-id=\"{pengajuan_id}\"\n";
echo "   data-layanan-id=\"{pl.id}\"\n\n";

echo "4. Expected Form Submission:\n";
echo "   For new entry (pl.id = NULL):\n";
echo "   ajax=1&id=&pengajuan_id=103&output=5&utama=120&tambahan=30&jenis=Disetujui&keterangan=...\n\n";
echo "   For existing entry (pl.id = 1):\n";
echo "   ajax=1&id=1&pengajuan_id=103&output=10&utama=200&tambahan=50&jenis=Disetujui&keterangan=...\n\n";

echo "✅ DEBUGGING COMPLETE\n";
?>
