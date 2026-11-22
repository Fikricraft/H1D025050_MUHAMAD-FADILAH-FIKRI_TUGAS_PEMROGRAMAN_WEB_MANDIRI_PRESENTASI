from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE
from docx import Document
from docx.shared import Inches as DInches, Pt as DPt, RGBColor as DRGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.section import WD_SECTION
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
import os, re

ROOT=os.path.dirname(os.path.abspath(__file__))
OUT=os.path.join(ROOT,'Presentasi_IKAN_Protocol_Final.pptx')
DOC=os.path.join(ROOT,'Penjelasan_Presentasi_IKAN_Protocol_Final.docx')
IMG=os.path.join(ROOT,'assets','img','ppt')
NAVY=RGBColor(3,8,17); CYAN=RGBColor(0,210,255); BLUE=RGBColor(82,229,255); WHITE=RGBColor(240,240,240); MUTED=RGBColor(157,177,194); PANEL=RGBColor(9,22,37); GREEN=RGBColor(72,220,151); ORANGE=RGBColor(255,177,77)

def add_text(slide,text,x,y,w,h,size=18,color=WHITE,bold=False,font='Aptos',align=PP_ALIGN.LEFT):
    box=slide.shapes.add_textbox(Inches(x),Inches(y),Inches(w),Inches(h)); tf=box.text_frame; tf.clear(); tf.word_wrap=True
    p=tf.paragraphs[0]; p.alignment=align; r=p.add_run(); r.text=text; r.font.name=font; r.font.size=Pt(size); r.font.bold=bold; r.font.color.rgb=color
    return box

def panel(slide,x,y,w,h,fill=PANEL,line=RGBColor(20,55,77)):
    s=slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE,Inches(x),Inches(y),Inches(w),Inches(h)); s.fill.solid(); s.fill.fore_color.rgb=fill; s.line.color.rgb=line; s.line.width=Pt(1); return s

def bullets(slide,items,x,y,w,h,size=14):
    box=slide.shapes.add_textbox(Inches(x),Inches(y),Inches(w),Inches(h)); tf=box.text_frame; tf.clear(); tf.word_wrap=True
    for i,item in enumerate(items):
        p=tf.paragraphs[0] if i==0 else tf.add_paragraph(); p.text=item; p.level=0; p.font.name='Aptos'; p.font.size=Pt(size); p.font.color.rgb=WHITE; p.space_after=Pt(8); p.bullet=True
    return box

def image(slide,name,x,y,w,h):
    path=os.path.join(IMG,name)
    if os.path.exists(path): slide.shapes.add_picture(path,Inches(x),Inches(y),width=Inches(w),height=Inches(h))

def base(prs,num,section,title,kicker='IKAN// PROTOCOL'):
    slide=prs.slides.add_slide(prs.slide_layouts[6]); bg=slide.background.fill; bg.solid(); bg.fore_color.rgb=NAVY
    add_text(slide,f'{num:02d}  /  {section.upper()}',0.55,0.28,5.5,0.3,10,CYAN,True,'Consolas')
    add_text(slide,title,0.55,0.68,12.0,0.55,26,WHITE,True,'Aptos Display')
    add_text(slide,kicker,11.0,0.32,2.0,0.25,9,MUTED,True,'Consolas',PP_ALIGN.RIGHT)
    line=slide.shapes.add_shape(MSO_SHAPE.RECTANGLE,Inches(.55),Inches(1.38),Inches(12.2),Inches(.015)); line.fill.solid(); line.fill.fore_color.rgb=CYAN; line.line.fill.background()
    add_text(slide,str(num),12.35,7.05,.4,.2,9,MUTED,True,'Consolas',PP_ALIGN.RIGHT)
    return slide

def note(slide,text):
    try:
        tf=slide.notes_slide.notes_text_frame; tf.text=text
    except Exception: pass

def codebox(slide,code,x,y,w,h):
    panel(slide,x,y,w,h,RGBColor(2,14,25),RGBColor(0,90,120)); add_text(slide,code,x+.18,y+.12,w-.35,h-.2,11,BLUE,False,'Consolas')

slides=[]
prs=Presentation(); prs.slide_width= Inches(13.333); prs.slide_height= Inches(7.5)
# 1
s=base(prs,1,'Project Brief','IKAN// PROTOCOL','PEMROGRAMAN WEB MANDIRI')
add_text(s,'FISH HABIT TRACKER\n& AQUATIC BIO-CARE',.7,1.75,7.4,1.25,30,CYAN,True,'Aptos Display'); add_text(s,'Aplikasi pencatatan kebiasaan perawatan ikan berbasis\nPHP Native, MySQL, dan Vanilla JavaScript.',.72,3.2,6.4,.7,16,WHITE)
panel(s,8.4,1.75,4.1,3.7); add_text(s,'DISUSUN OLEH',8.75,2.1,3.4,.3,10,MUTED,True,'Consolas'); add_text(s,'Muhamad Fadilah Fikri',8.75,2.55,3.2,.4,18,WHITE,True); add_text(s,'NIM  H1D025050',8.75,3.05,3.2,.35,14,CYAN,True,'Consolas'); add_text(s,'100% PURE NATIVE\nHTML5  /  CSS3  /  JS DOM\nPHP  /  MySQL',8.75,4.05,3.2,1.0,13,BLUE,True,'Consolas'); note(s,'Pada slide pertama saya memperkenalkan IKAN Protocol sebagai proyek akhir Pemrograman Web Mandiri. Aplikasi ini dibuat tanpa framework, menggunakan HTML, CSS, Vanilla JavaScript, PHP Native, dan MySQL. Tujuan utamanya adalah membantu pemilik ikan mencatat kebiasaan perawatan secara teratur. Identitas penyusun adalah Muhamad Fadilah Fikri dengan NIM H1D025050.')
#2
s=base(prs,2,'Problem','Latar Belakang & Masalah')
panel(s,.7,1.75,5.8,4.8); add_text(s,'MASALAH DI LAPANGAN',1.0,2.05,4.8,.3,11,ORANGE,True,'Consolas'); bullets(s,['Jadwal pakan dan perawatan mudah terlewat.','Parameter air berubah, tetapi riwayatnya tersebar.','Catatan manual sulit dipakai untuk evaluasi.','Data spesimen, habit, dan laporan tidak terhubung.'],1.0,2.55,5.0,2.8,16)
panel(s,6.8,1.75,5.8,4.8); add_text(s,'RESPON SISTEM',7.1,2.05,4.8,.3,11,CYAN,True,'Consolas'); bullets(s,['Checklist harian dengan status tersimpan.','Riwayat terstruktur pada database relasional.','Metrik streak dan persentase kepatuhan.','CSV export untuk analisis lanjutan.'],7.1,2.55,5.0,2.8,16); note(s,'Masalah utama yang saya angkat adalah perawatan ikan membutuhkan konsistensi. Pakan, pemeriksaan air, dan sanitasi dilakukan berulang, sehingga mudah terlupakan jika hanya mengandalkan ingatan atau catatan yang tersebar. IKAN Protocol merespons masalah tersebut dengan checklist harian, penyimpanan riwayat, metrik kepatuhan, dan ekspor CSV.')
#3
s=base(prs,3,'Scope','Pemenuhan Modul 1–7')
rows=[('01','HTML','struktur semantik, tabel, form'),('02','CSS','variable, grid, flexbox, responsif'),('03','JavaScript','DOM, event, validasi, Fetch API'),('04','PHP Dasar','include, function, if/else, loop'),('05','CRUD + MySQL','prepared statement, 4 modul data'),('06','Session','login guard, regenerate ID'),('07','Cookie & Security','bcrypt, HMAC, escaping XSS')]
for i,(n,a,b) in enumerate(rows):
 y=1.72+i*.62; panel(s,.8,y,1.0,.44,RGBColor(0,55,75)); add_text(s,n,1.02,y+.08,.55,.2,11,CYAN,True,'Consolas',PP_ALIGN.CENTER); add_text(s,a,2.05,y+.07,2.0,.25,13,WHITE,True); add_text(s,b,4.25,y+.07,7.8,.25,13,MUTED)
add_text(s,'KONSEP UTAMA',.85,6.18,2,.25,10,CYAN,True,'Consolas'); add_text(s,'Satu proyek — seluruh materi praktikum terintegrasi.',2.4,6.14,8,.3,16,WHITE,True); note(s,'Slide ini memetakan implementasi terhadap modul praktikum. Modul HTML diterapkan pada struktur dan formulir. CSS mengatur tema serta layout responsif. JavaScript menangani DOM dan Fetch API. PHP menerapkan logika dan perulangan. CRUD memakai MySQL prepared statements. Session menjaga akses pengguna, sementara bcrypt, HMAC cookie, dan escaping memperkuat keamanan.')
#4
s=base(prs,4,'Architecture','Arsitektur Sistem & Aliran Data')
for x,label,sub,col in [(0.8,'CLIENT','HTML / CSS / DOM',BLUE),(4.45,'SERVER','PHP Native / API',CYAN),(8.1,'DATABASE','MySQL Relational',GREEN)]:
 panel(s,x,2.1,3.0,2.1); add_text(s,label,x+.25,2.48,2.5,.35,18,col,True,'Consolas'); add_text(s,sub,x+.25,3.15,2.5,.35,13,WHITE); add_text(s,'→' if x<8 else '✓',x+1.25,4.48,.5,.3,24,col,True,'Consolas',PP_ALIGN.CENTER)
add_text(s,'Fetch API',3.55,2.75,.8,.25,10,MUTED,True,'Consolas',PP_ALIGN.CENTER); add_text(s,'Prepared SQL',7.2,2.75,.9,.25,10,MUTED,True,'Consolas',PP_ALIGN.CENTER)
codebox(s,"fetch('api.php', { method: 'POST',\n  body: formData\n})",.9,5.0,5.1,1.2); codebox(s,"SELECT ... WHERE user_id = ?",7.1,5.0,5.0,1.2); note(s,'Aliran data dimulai dari antarmuka browser. Pengguna berinteraksi dengan DOM, lalu JavaScript mengirim permintaan menggunakan Fetch API. PHP memvalidasi session dan input, kemudian menjalankan query melalui prepared statement. Hasilnya dikembalikan sebagai HTML atau JSON dan ditampilkan kembali pada halaman.')
#5
s=base(prs,5,'Data Design','Perancangan Database Relasional')
entities=[('users','akun pengguna'),('habits','protokol habit'),('habit_logs','riwayat harian'),('fishes','spesimen ikan'),('water_params','parameter air'),('feedings','log pakan'),('pakan_jenis','master pakan')]
for i,(a,b) in enumerate(entities):
 x=.8+(i%4)*3.05; y=1.85+(i//4)*1.45; panel(s,x,y,2.65,.82,RGBColor(7,30,44)); add_text(s,a,x+.16,y+.16,2.3,.25,13,CYAN,True,'Consolas'); add_text(s,b,x+.16,y+.48,2.3,.2,10,MUTED)
codebox(s,'UNIQUE(habit_id, tanggal)\nFOREIGN KEY (...) REFERENCES ...\nON DELETE CASCADE',.9,5.05,5.8,1.25); add_text(s,'Integritas data:',7.2,5.1,2,.25,12,GREEN,True); bullets(s,['Satu habit satu log per tanggal.','Data pengguna terisolasi dengan user_id.','Cascade membersihkan data turunan.'],7.2,5.45,4.8,.8,13); note(s,'Database fish_habit_tracker terdiri dari tujuh tabel. Tabel users menjadi pusat kepemilikan data. Habits memiliki habit_logs, sedangkan fishes, water_params, feedings, dan pakan_jenis menyimpan domain perawatan lain. Constraint unik mencegah duplikasi, foreign key menjaga relasi, dan cascade menjaga konsistensi ketika data induk dihapus.')
#6
s=base(prs,6,'Security','Autentikasi, Session & Cookie')
image(s,'login_crop.png',.8,1.8,5.5,3.05); panel(s,6.7,1.8,5.7,3.05); bullets(s,['password_hash() dengan bcrypt.','password_verify() saat login.','session_regenerate_id() setelah autentikasi.','auth.php sebagai session guard.','Cookie Remember Me ditandatangani HMAC.'],7.05,2.15,4.9,2.1,15)
codebox(s,"password_hash($password, PASSWORD_DEFAULT);\nif (password_verify($password, $hash)) {\n  session_regenerate_id(true);\n}",.9,5.35,11.5,1.05); note(s,'Keamanan autentikasi dibangun pada beberapa lapisan. Password tidak disimpan dalam bentuk teks biasa, melainkan hash bcrypt. Saat login, password diverifikasi dengan password_verify. Setelah berhasil, session ID diregenerasi untuk mencegah session fixation. Halaman privat dilindungi auth.php. Cookie ingat saya membawa tanda tangan HMAC sehingga perubahan isi cookie dapat dideteksi.')
#7
s=base(prs,7,'Logic','Algoritma Pemilihan & Perulangan')
codebox(s,"if ($ph < 6.5 || $ph > 8.5) {\n  $status = 'BAHAYA';\n} else { $status = 'IDEAL'; }",.8,1.85,5.65,1.65); codebox(s,"switch ($kategori) {\n case 'pakan': ...; break;\n case 'air': ...; break;\n default: ...;\n}",6.85,1.85,5.65,1.65); codebox(s,"for ($i = 6; $i >= 0; $i--) {\n  $tanggal = date('Y-m-d', strtotime('-'.$i.' days'));\n  $matrix[] = $tanggal;\n}",.8,4.0,5.65,1.65); codebox(s,"foreach ($rows as $row) {\n  renderHabit($row);\n}",6.85,4.0,5.65,1.65); note(s,'Pemenuhan algoritma terlihat pada logika kualitas air dan pengolahan laporan. If else digunakan untuk menentukan kondisi ideal atau bahaya. Switch dapat memetakan kategori tindakan. For membentuk rentang tujuh hari secara dinamis. Foreach mengiterasi hasil query untuk membangun tabel dan checklist.')
#8
s=base(prs,8,'Feature','Dashboard Real-time & 3D WebGL')
image(s,'dashboard_panel.png',.75,1.75,7.0,4.55); panel(s,8.15,1.75,4.45,4.55); add_text(s,'DASHBOARD CORE',8.5,2.1,3.4,.3,11,CYAN,True,'Consolas'); bullets(s,['Checklist habit harian.','Progress dan metrik streak.','Update tanpa reload melalui API.','Model alien_fish_animated.glb.','Status database / fallback demo.'],8.5,2.6,3.55,2.2,15); codebox(s,"document.querySelector('#habit-list')\n  .addEventListener('click', toggleHabit);",8.35,5.25,3.95,.75); note(s,'Dashboard adalah pusat interaksi aplikasi. Daftar habit dapat dicentang, progress dihitung, dan data diperbarui tanpa reload penuh. Visual 3D menggunakan model alien_fish_animated.glb melalui model-viewer. Fitur visual ini tidak memengaruhi penyimpanan data, tetapi memperkuat identitas cyberpunk dan membuat halaman lebih hidup.')
#9
s=base(prs,9,'Feature','CRUD Spesimen Ikan & Master Habit')
image(s,'ikan_crop.png',.75,1.8,5.8,3.25); image(s,'form_crop.png',6.85,1.8,5.75,3.25); add_text(s,'CREATE',1.0,5.45,1.2,.25,11,GREEN,True,'Consolas'); add_text(s,'READ',3.0,5.45,1.2,.25,11,CYAN,True,'Consolas'); add_text(s,'UPDATE',5.0,5.45,1.5,.25,11,BLUE,True,'Consolas'); add_text(s,'DELETE',7.3,5.45,1.5,.25,11,ORANGE,True,'Consolas'); add_text(s,'Data ikan: nama, jenis, ukuran, umur, kondisi, catatan.  |  Habit: nama, kategori, waktu, target harian.',1.0,5.9,11.4,.35,13,WHITE); note(s,'Modul CRUD dipisahkan berdasarkan domain. Folder ikan menangani daftar, tambah, edit, detail, dan hapus spesimen. Folder habit menangani master protokol perawatan. Semua operasi menggunakan identitas user dari session agar data antar akun tidak tercampur.')
#10
s=base(prs,10,'Feature','Kualitas Air & Parameter Kimia')
image(s,'dashboard_full.png',.75,1.75,5.0,4.85); panel(s,6.1,1.75,6.35,4.85); add_text(s,'PARAMETER YANG DICATAT',6.5,2.1,4.5,.3,11,CYAN,True,'Consolas'); bullets(s,['pH — tingkat keasaman air.','Suhu — temperatur akuarium.','Amonia, nitrit, nitrat — indikator kimia.','Satu catatan per user per tanggal.','Tren tujuh hari untuk evaluasi.'],6.5,2.6,5.1,2.0,16); codebox(s,"UNIQUE KEY unique_user_date\n(user_id, tanggal)",6.5,5.15,5.2,.9); note(s,'Modul air mencatat parameter penting seperti pH, suhu, amonia, nitrit, dan nitrat. Data disimpan per pengguna dan tanggal. Constraint unik memastikan tidak ada dua catatan air untuk tanggal yang sama pada akun yang sama. Riwayat tujuh hari membantu pengguna melihat tren, bukan hanya satu nilai terisolasi.')
#11
s=base(prs,11,'Feature','Log Pakan, Laporan & CSV Export')
image(s,'laporan_crop.png',.75,1.75,7.0,4.7); panel(s,8.1,1.75,4.45,4.7); add_text(s,'LAPORAN ANALITIK',8.45,2.1,3.5,.3,11,CYAN,True,'Consolas'); bullets(s,['Rentang 7, 30, atau 90 hari.','Total checklist dan rata-rata harian.','Hari sempurna dan streak.','Matriks kepatuhan 7 hari.','Ekspor CSV untuk spreadsheet.'],8.45,2.6,3.5,2.2,15); add_text(s,'feedings  →  laporan.php  →  CSV',8.45,5.35,3.7,.3,13,GREEN,True,'Consolas'); note(s,'Log pakan mencatat tanggal, waktu, jenis pakan, jumlah, satuan, dan status habis atau sisa. Laporan mengubah data mentah menjadi metrik yang lebih mudah dipahami: jumlah checklist, rata-rata, hari sempurna, streak, dan matriks tujuh hari. Data juga dapat diekspor ke CSV untuk diproses di spreadsheet.')
#12
s=base(prs,12,'Quality','Keamanan & Kualitas Implementasi')
panel(s,.75,1.8,5.85,4.7); add_text(s,'PROTEKSI INPUT',1.05,2.15,4.5,.3,11,CYAN,True,'Consolas'); bullets(s,['MySQLi prepared statements.','htmlspecialchars() untuk output HTML.','Validasi sisi klien dan server.','DELETE hanya melalui POST.'],1.05,2.65,4.8,1.8,16); panel(s,6.85,1.8,5.75,4.7); add_text(s,'RESILIENSI DEMO',7.15,2.15,4.5,.3,11,GREEN,True,'Consolas'); bullets(s,['Fallback demo saat database belum aktif.','Status koneksi ditampilkan jelas.','Local storage menjaga alur uji coba.','Error tidak ditampilkan sebagai query mentah.'],7.15,2.65,4.7,1.8,16); codebox(s,"$safe = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');",1.05,5.25,11.1,.8); note(s,'Keamanan tidak hanya terkait login. Query menggunakan prepared statements untuk mencegah SQL injection. Output disaring dengan htmlspecialchars untuk mengurangi risiko XSS. Penghapusan data menggunakan POST, bukan GET. Saat database belum tersedia, aplikasi masuk fallback demo mode dan memberi penanda yang jelas sehingga presentasi tetap dapat dilakukan tanpa menyamarkan status penyimpanan.')
#13
s=base(prs,13,'Closing','Kesimpulan & Demo')
add_text(s,'SATU APLIKASI.\nTUJUH MATERI.\nDATA TERUKUR.',.8,1.9,6.3,1.7,30,CYAN,True,'Aptos Display'); panel(s,7.5,1.8,4.9,3.5); bullets(s,['Pure Native tanpa framework.','7 tabel relasional dan CRUD.','Session, cookie, dan security guard.','Dashboard, laporan, dan CSV.'],7.9,2.25,4.0,2.0,16); add_text(s,'DEMO FLOW',.85,5.2,1.5,.25,11,ORANGE,True,'Consolas'); add_text(s,'Login → Dashboard → Checklist → Air → Ikan → Laporan → CSV',2.35,5.16,9.5,.3,14,WHITE,True); add_text(s,'Terima kasih — sesi tanya jawab.',.85,6.15,5,.35,18,WHITE,True); note(s,'Sebagai kesimpulan, IKAN Protocol mengintegrasikan tujuh materi praktikum menjadi satu aplikasi yang berjalan. Fitur utamanya mencakup autentikasi, dashboard checklist, CRUD ikan dan habit, pemantauan air, log pakan, laporan, dan ekspor CSV. Pada demo, alurnya adalah login, membuka dashboard, mencentang habit, melihat modul air dan ikan, lalu membuka laporan dan mencoba ekspor CSV. Setelah itu saya siap menjawab pertanyaan.')

prs.save(OUT)

# DOCX speaker script
D=Document(); sec=D.sections[0]; sec.top_margin=DInches(.7); sec.bottom_margin=DInches(.7); sec.left_margin=DInches(.85); sec.right_margin=DInches(.85)
styles=D.styles; styles['Normal'].font.name='Arial'; styles['Normal'].font.size=DPt(10.5)
t=D.add_heading('PENJELASAN PRESENTASI',0); t.alignment=WD_ALIGN_PARAGRAPH.CENTER
p=D.add_paragraph('IKAN// PROTOCOL — Fish Habit Tracker & Aquatic Bio-Care Management'); p.alignment=WD_ALIGN_PARAGRAPH.CENTER
p=D.add_paragraph('Muhamad Fadilah Fikri  |  H1D025050'); p.alignment=WD_ALIGN_PARAGRAPH.CENTER
D.add_paragraph('Naskah pembicaraan per slide untuk presentasi Pemrograman Web Mandiri.',style='Subtitle')
notes=[
'Pada slide pertama saya memperkenalkan IKAN Protocol sebagai proyek akhir Pemrograman Web Mandiri. Aplikasi ini dibuat tanpa framework, menggunakan HTML, CSS, Vanilla JavaScript, PHP Native, dan MySQL. Tujuan utamanya adalah membantu pemilik ikan mencatat kebiasaan perawatan secara teratur. Identitas penyusun adalah Muhamad Fadilah Fikri dengan NIM H1D025050.',
'Masalah utama yang saya angkat adalah perawatan ikan membutuhkan konsistensi. Pakan, pemeriksaan air, dan sanitasi dilakukan berulang, sehingga mudah terlupakan jika hanya mengandalkan ingatan atau catatan yang tersebar. IKAN Protocol merespons masalah tersebut dengan checklist harian, penyimpanan riwayat, metrik kepatuhan, dan ekspor CSV.',
'Slide ini memetakan implementasi terhadap modul praktikum. Modul HTML diterapkan pada struktur dan formulir. CSS mengatur tema serta layout responsif. JavaScript menangani DOM dan Fetch API. PHP menerapkan logika dan perulangan. CRUD memakai MySQL prepared statements. Session menjaga akses pengguna, sementara bcrypt, HMAC cookie, dan escaping memperkuat keamanan.',
'Aliran data dimulai dari antarmuka browser. Pengguna berinteraksi dengan DOM, lalu JavaScript mengirim permintaan menggunakan Fetch API. PHP memvalidasi session dan input, kemudian menjalankan query melalui prepared statement. Hasilnya dikembalikan sebagai HTML atau JSON dan ditampilkan kembali pada halaman.',
'Database fish_habit_tracker terdiri dari tujuh tabel. Tabel users menjadi pusat kepemilikan data. Habits memiliki habit_logs, sedangkan fishes, water_params, feedings, dan pakan_jenis menyimpan domain perawatan lain. Constraint unik mencegah duplikasi, foreign key menjaga relasi, dan cascade menjaga konsistensi ketika data induk dihapus.',
'Keamanan autentikasi dibangun pada beberapa lapisan. Password tidak disimpan dalam bentuk teks biasa, melainkan hash bcrypt. Saat login, password diverifikasi dengan password_verify. Setelah berhasil, session ID diregenerasi untuk mencegah session fixation. Halaman privat dilindungi auth.php. Cookie ingat saya membawa tanda tangan HMAC sehingga perubahan isi cookie dapat dideteksi.',
'Pemenuhan algoritma terlihat pada logika kualitas air dan pengolahan laporan. If else digunakan untuk menentukan kondisi ideal atau bahaya. Switch dapat memetakan kategori tindakan. For membentuk rentang tujuh hari secara dinamis. Foreach mengiterasi hasil query untuk membangun tabel dan checklist.',
'Dashboard adalah pusat interaksi aplikasi. Daftar habit dapat dicentang, progress dihitung, dan data diperbarui tanpa reload penuh. Visual 3D menggunakan model alien_fish_animated.glb melalui model-viewer. Fitur visual ini tidak memengaruhi penyimpanan data, tetapi memperkuat identitas cyberpunk dan membuat halaman lebih hidup.',
'Modul CRUD dipisahkan berdasarkan domain. Folder ikan menangani daftar, tambah, edit, detail, dan hapus spesimen. Folder habit menangani master protokol perawatan. Semua operasi menggunakan identitas user dari session agar data antar akun tidak tercampur.',
'Modul air mencatat parameter penting seperti pH, suhu, amonia, nitrit, dan nitrat. Data disimpan per pengguna dan tanggal. Constraint unik memastikan tidak ada dua catatan air untuk tanggal yang sama pada akun yang sama. Riwayat tujuh hari membantu pengguna melihat tren, bukan hanya satu nilai terisolasi.',
'Log pakan mencatat tanggal, waktu, jenis pakan, jumlah, satuan, dan status habis atau sisa. Laporan mengubah data mentah menjadi metrik yang lebih mudah dipahami: jumlah checklist, rata-rata, hari sempurna, streak, dan matriks tujuh hari. Data juga dapat diekspor ke CSV untuk diproses di spreadsheet.',
'Keamanan tidak hanya terkait login. Query menggunakan prepared statements untuk mencegah SQL injection. Output disaring dengan htmlspecialchars untuk mengurangi risiko XSS. Penghapusan data menggunakan POST, bukan GET. Saat database belum tersedia, aplikasi masuk fallback demo mode dan memberi penanda yang jelas sehingga presentasi tetap dapat dilakukan tanpa menyamarkan status penyimpanan.',
'Sebagai kesimpulan, IKAN Protocol mengintegrasikan tujuh materi praktikum menjadi satu aplikasi yang berjalan. Fitur utamanya mencakup autentikasi, dashboard checklist, CRUD ikan dan habit, pemantauan air, log pakan, laporan, dan ekspor CSV. Pada demo, alurnya adalah login, membuka dashboard, mencentang habit, melihat modul air dan ikan, lalu membuka laporan dan mencoba ekspor CSV. Setelah itu saya siap menjawab pertanyaan.'
]
for i,(title,body) in enumerate(zip(['Cover & Identitas','Latar Belakang & Masalah','Pemenuhan Modul 1–7','Arsitektur Sistem','Database Relasional','Autentikasi & Session','Algoritma','Dashboard & WebGL','CRUD Ikan & Habit','Kualitas Air','Pakan & Laporan','Keamanan','Kesimpulan & Demo'],notes),1):
 D.add_heading(f'Slide {i} — {title}',1); D.add_paragraph(body)
D.add_heading('Urutan Demo Praktis',1); D.add_paragraph('Login → Dashboard → centang habit → tambah/lihat parameter air → lihat data ikan → buka laporan → ekspor CSV → kembali ke dashboard.')
D.add_heading('Catatan Teknis',1); D.add_paragraph('Aplikasi berada pada folder proyek yang diberikan. Screenshot pada PPTX diambil dari assets/img/ppt/. Proyek menggunakan PHP Native, MySQL, CSS, dan Vanilla JavaScript tanpa framework.')
D.save(DOC)
print(OUT); print(DOC)
