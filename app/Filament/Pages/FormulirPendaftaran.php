<?php
namespace App\Filament\Pages;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Pages\Actions\Action;
use Filament\Notifications\Notification; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Formulir;
use App\Models\Periode;
class FormulirPendaftaran extends Page
{
 protected static ?string $navigationIcon = 'heroicon-o-document-text';
 protected static string $view = 'filament.pages.formulir-pendaftaran';
 

    //tampilkan menu navigasi jika user memiliki role 'Pendaftar'
    protected static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('Pendaftar');
    }

    public function mount(): void
    {
    //tolak akses halaman jika user tidak memiliki role 'Pendaftar'
    abort_unless(
        Auth::user()->hasRole('Pendaftar'), 
        403
     );
        //ambil periode aktif
        $periode = Periode::where("aktif",1)->first();

        //ambil data formulir dari periode dan user yang login
        $formulir = Formulir::where("id_periode", $periode->id)
                        ->where("id_user", Auth::user()->id)
                        ->first();

        //jika tidak ada data formulir, maka buat data awal periode dan nama
        if (!$formulir) {

                $data_awal = [
                    'id_periode' => $periode->id,
                    'nama' => Auth::user()->name
                ];

        } else {
        $data_awal = $formulir ->toArray(); //data awal pakai data di database
        }

        $this->form->fill($data_awal); //data awal dimasukkan ke form
    }

    protected function getFormModel(): string 
    {
    return Formulir::class;
    }
    protected function getFormSchema(): array
{
 return [
    Forms\Components\TextInput::make('no_daftar')
        ->label('No Daftar (otomatis)')
        ->disabled(),
    Forms\Components\TextInput::make('id_periode')
        ->label('Periode')
        ->disabled()
        ->required(),   
    Forms\Components\TextInput::make('nama')
        ->label('Nama Lengkap')
        ->required(),
    Forms\Components\Radio::make('jenis_kelamin')
        ->label('Jenis Kelamin')
        ->options([
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        ])->required(),
    Forms\Components\TextInput::make('tempat_lahir')
        ->label('Tempat Lahir')
        ->required(),
    Forms\Components\DatePicker::make('tanggal_lahir')
        ->label('Tanggal Lahir')
        ->displayFormat('d/m/Y')
        ->required(),
    Forms\Components\TextArea::make('alamat')
        ->label('Alamat Lengkap')
        ->required(),
    Forms\Components\TextInput::make('telp')
        ->label('Telp/WA')
        ->tel()
        ->required(),
    
    Forms\Components\Select::make('program_studi')
        ->label('Pilihan Program Studi')
        ->relationship("programStudi","kode_prodi")
        ->getOptionLabelFromRecordUsing( function (Model $record) {
                    return $record->jenjang->nama_jenj_didik." - ".$record->nama_prodi;
    })
        ->preload()
        ->required(),
 ];
}

}
