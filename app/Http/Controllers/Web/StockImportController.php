<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockImport;
use App\Services\StockImportService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Branch;

class StockImportController extends Controller
{
    public function __construct(private readonly StockImportService $service)
    {
    }

    public function index()
{
    $imports = StockImport::with('user')
        ->latest()
        ->paginate(10);

    $branches = Branch::orderBy('name')->get();

    return view('imports.index', compact('imports', 'branches'));
}

    /** İlk yükleme. Upsert doğrudan işlenir, Replace onay ekranına düşer. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'mode' => 'required|in:upsert,replace',
        ]);

        $result = $this->service->process(
            file: $data['file'],
            mode: $data['mode'],
            company: $request->user()->company,
            user: $request->user(),
            confirm: false
        );

        return $this->handleResult($result, $data['file'], $data['mode']);
    }

    /**
     * Replace onayı. Dosyayı tekrar yükletmemek için store() sırasında
     * geçici olarak saklanan dosyayı buradan okuyup aynı servise
     * confirm: true ile veriyoruz.
     */
    public function confirm(Request $request)
    {
        $data = $request->validate([
            'temp_path' => 'required|string',
            'original_name' => 'required|string',
            'mode' => 'required|in:upsert,replace',
        ]);

        $fullPath = Storage::disk('local')->path($data['temp_path']);

        if (! is_file($fullPath)) {
            return back()->withErrors(['file' => 'Geçici dosya bulunamadı, lütfen tekrar yükle.']);
        }

        $file = new UploadedFile($fullPath, $data['original_name'], null, null, true);

        $result = $this->service->process(
            file: $file,
            mode: $data['mode'],
            company: $request->user()->company,
            user: $request->user(),
            confirm: true
        );

        Storage::disk('local')->delete($data['temp_path']);

        return $this->handleResult($result, $file, $data['mode']);
    }

    private function handleResult(array $result, UploadedFile $file, string $mode)
    {
        if ($result['status'] === 'format_error') {
            return back()->withErrors(['file' => $result['message']]);
        }

        if ($result['status'] === 'confirmation_required') {
            // Dosyayı geçici olarak sakla ki onay adımında kullanıcı
            // aynı dosyayı tekrar yüklemek zorunda kalmasın.
            $tempPath = $file->store('imports-tmp');

            return view('imports.confirm', [
                'result' => $result,
                'temp_path' => $tempPath,
                'original_name' => $file->getClientOriginalName(),
                'mode' => $mode,
            ]);
        }

        return redirect()->route('imports.index')->with('status', sprintf(
            'İçe aktarım tamamlandı: %d/%d satır başarılı, %d hata%s.',
            $result['success_rows'],
            $result['total_rows'],
            $result['error_rows'],
            $mode === 'replace' ? sprintf(', %d ürün pasif yapıldı', $result['deactivated_count']) : ''
        ))->with('import_errors', $result['errors']);
    }
}
