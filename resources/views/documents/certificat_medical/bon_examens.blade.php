{{-- resources/views/pdf/documents/projet_lettre_agrement.blade.php --}}
{{-- Étend le layout PDF de base existant --}}
@extends('documents.pdfBase')

@section('body-title')
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="font-size: 14px; font-weight: bold; text-transform: uppercase;">
            REPUBLIQUE DU BENIN
        </h2>
        <p style="font-size: 11px; margin: 4px 0;">
            Ministère du Travail et de la Fonction Publique
        </p>
        <p style="font-size: 11px; margin: 4px 0;">
            Direction de la Santé et de la Sécurité au Travail
        </p>
        <hr style="border: 1px solid #000; margin: 10px 0;">
        <h3 style="font-size: 13px; font-weight: bold; text-transform: uppercase; margin-top: 20px;">
            BON D'EXAMENS MÉDICAUX
        </h3>
        <p style="font-size: 11px;">
            N° {{ $numero ?? '___/MTFP/DSSMST/SA' }} &nbsp;&nbsp; Cotonou, le {{ $date ?? '' }}
        </p>
    </div>
@endsection

@section('body-content')
    <div style="font-size: 11px; line-height: 1.8; text-align: justify;">

        {{-- En-tête destinataire --}}
        <div style="margin-bottom: 20px;">
            <p><strong>Objet :</strong> Agrément en qualité de médecin d'entreprise</p>
        </div>

        {{-- Corps du document --}}
        @if(isset($content) && $content)
            {!! $content !!}
        @else
            <p>Monsieur/Madame <strong>{{ $variables['Nom'] ?? '[NOM PRÉNOM]' }}</strong>,</p>

            <p>
                J'ai l'honneur de vous informer que votre demande d'agrément en qualité de
                médecin d'entreprise auprès de l'établissement
                <strong>{{ $variables['Dénomination de l\'établissement'] ?? '[DÉNOMINATION ÉTABLISSEMENT]' }}</strong>,
                sis à <strong>{{ $variables['Adresse'] ?? '[ADRESSE]' }}</strong>, a été examinée
                par les services compétents de la Direction de la Santé et de la Sécurité au Travail.
            </p>

            <p>
                Au regard des pièces constitutives de votre dossier et des dispositions
                réglementaires en vigueur, il vous est accordé l'agrément sollicité sous
                réserve du respect des obligations légales relatives à l'exercice de la
                médecine du travail au Bénin.
            </p>

            <p>
                Vous êtes invité(e) à vous conformer aux dispositions du Code du Travail
                et des textes pris pour son application en ce qui concerne la médecine
                du travail.
            </p>
        @endif

        {{-- Conclusion --}}
        @if(isset($conclusion) && $conclusion)
            <div style="margin-top: 20px;">
                {!! $conclusion !!}
            </div>
        @else
            <p style="margin-top: 30px;">
                Veuillez agréer, Monsieur/Madame, l'expression de ma considération distinguée.
            </p>
        @endif

    </div>
@endsection

@section('body-signature')
    <div style="margin-top: 40px; float: right; text-align: center; width: 250px;">
        <p style="font-size: 11px;">Le Ministre du Travail et de la Fonction Publique</p>
        <br><br><br>
        <p style="font-size: 11px; font-weight: bold;">
            {{ $variables['ministre_nom'] ?? '[NOM DU MINISTRE]' }}
        </p>
    </div>
@endsection