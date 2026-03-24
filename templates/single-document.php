<?php
/**
 * Template para exibição de Orçamentos e Contratos (ERP)
 * Design: Premium High-Tech Responsive
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$doc_id = get_the_ID();
$type   = get_post_meta($doc_id, '_acro_doc_type', true);
$client = get_post_meta($doc_id, '_acro_client_name', true);
$items  = get_post_meta($doc_id, '_acro_items', true) ?: [];
$total  = get_post_meta($doc_id, '_acro_total', true);
$terms  = get_post_meta($doc_id, '_acro_terms', true);
$status = get_post_meta($doc_id, '_acro_status', true);

// Cores Dinâmicas
$color = ($type === 'contrato') ? '#4f46e5' : '#0ea5e9'; // Indigo ou Sky
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php the_title(); ?> | Proposta Comercial</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background: #0f172a; color: #f1f5f9; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); }
        .gradient-text { background: linear-gradient(135deg, #fff 0%, <?php echo $color; ?> 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .glass { background: #fff !important; border: 1px solid #eee !important; color: #000 !important; backdrop-filter: none !important; }
            .gradient-text { -webkit-text-fill-color: #000 !important; }
        }
    </style>
</head>
<body class="min-h-screen p-4 md:p-12">

    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-700">
        
        <!-- HEADER -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-16">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/5 rounded-3xl flex items-center justify-center border border-white/10 shadow-2xl">
                     <i data-lucide="layers" class="w-8 h-8 text-sky-400"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tighter uppercase whitespace-nowrap"><?php echo get_bloginfo('name'); ?></h1>
                    <span class="text-[10px] uppercase font-black tracking-[0.3em] text-white/40">Solutions Hub</span>
                </div>
            </div>
            
            <div class="text-left md:text-right">
                <p class="text-[10px] uppercase font-black tracking-widest text-white/30 mb-2">Protocolo Eletrônico</p>
                <div class="inline-flex items-center gap-3 px-6 py-2 glass rounded-full border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-black uppercase tracking-widest">Ativo/Válido</span>
                    <span class="text-white/20">|</span>
                    <span class="text-xs font-black">#<?php echo $doc_id; ?></span>
                </div>
            </div>
        </header>

        <!-- TITLE CARD -->
        <section class="glass rounded-[40px] p-8 md:p-16 relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-sky-500/10 blur-[100px] rounded-full"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-indigo-500/10 blur-[100px] rounded-full"></div>

            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-[0.4em] text-sky-400 mb-4"><?php echo strtoupper($type); ?> COMERCIAL</p>
                <h2 class="text-4xl md:text-6xl font-black tracking-tighter mb-8 leading-none gradient-text"><?php the_title(); ?></h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mt-16 pt-12 border-t border-white/5">
                    <div>
                        <p class="text-[10px] uppercase font-black text-white/30 tracking-widest mb-4">Apresentado para:</p>
                        <p class="text-2xl font-black text-white"><?php echo $client; ?></p>
                        <p class="text-sm text-white/50 font-medium mt-1">Garantia de conformidade técnica e operacional.</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-white/30 tracking-widest mb-4">Emitido em:</p>
                        <p class="text-2xl font-black text-white"><?php echo get_the_date('M j, Y'); ?></p>
                        <p class="text-sm text-white/50 font-medium mt-1">Validade de 07 dias corridos.</p>
                    </div>
                </div>
            </div>
        </section> section

        <!-- ITEMS -->
        <section class="glass rounded-[40px] overflow-hidden">
            <header class="p-8 border-b border-white/5 bg-white/5">
                <h3 class="text-xs font-black uppercase tracking-widest text-sky-400 flex items-center gap-3"><i data-lucide="list" class="w-4 h-4"></i> Descrição do Escopo</h3>
            </header>
            <div class="p-8 md:p-12 space-y-6">
                <?php foreach($items as $idx => $item): ?>
                <div class="flex items-center justify-between gap-8 group">
                    <div class="flex items-center gap-6">
                        <span class="text-[10px] font-black text-white/10 group-hover:text-white/40 transition-colors uppercase tracking-widest">0<?php echo $idx+1; ?></span>
                        <p class="text-lg font-bold text-white/90 group-hover:text-white transition-colors"><?php echo esc_html($item['description']); ?></p>
                    </div>
                    <p class="text-xl font-black text-white whitespace-nowrap">R$ <?php echo number_format($item['price'], 2, ',', '.'); ?></p>
                </div>
                <div class="h-px bg-white/5 w-full"></div>
                <?php endforeach; ?>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 pt-12">
                    <div>
                        <h4 class="text-sm font-black text-white/30 uppercase tracking-widest">Investimento Total</h4>
                        <p class="text-5xl font-black tracking-tighter mt-2 text-white">R$ <?php echo number_format($total, 2, ',', '.'); ?></p>
                    </div>
                    <div class="flex gap-4 no-print">
                        <button onclick="window.print()" class="px-8 py-5 bg-white/5 text-white text-xs font-black rounded-3xl border border-white/10 hover:bg-white/10 transition-all uppercase tracking-widest flex items-center gap-3">
                            <i data-lucide="printer" class="w-4 h-4"></i> Imprimir/PDF
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- TERMS -->
        <?php if(!empty($terms)): ?>
        <section class="glass rounded-[40px] p-8 md:p-12">
             <h3 class="text-xs font-black uppercase tracking-widest text-white/30 mb-8 flex items-center gap-3"><i data-lucide="info" class="w-4 h-4 text-emerald-500"></i> Condições e Prazo</h3>
             <div class="text-white/60 text-sm leading-relaxed font-medium whitespace-pre-line prose prose-invert">
                 <?php echo esc_html($terms); ?>
             </div>
        </section>
        <?php endif; ?>

        <!-- FOOTER -->
        <footer class="text-center py-20 pb-40">
            <p class="text-[10px] font-black uppercase tracking-[0.5em] text-white/20 mb-8">Acromidia Manager ERP v3.x</p>
            <div class="flex justify-center gap-8 text-white/30">
                <i data-lucide="lock" class="w-4 h-4" title="Criptografado"></i>
                <i data-lucide="shield-check" class="w-4 h-4" title="Documento Verificado"></i>
                <i data-lucide="globe" class="w-4 h-4" title="Acesso Web"></i>
            </div>
        </footer>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
