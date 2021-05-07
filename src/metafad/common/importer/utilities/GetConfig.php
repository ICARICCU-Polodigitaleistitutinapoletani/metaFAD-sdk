<?php
class metafad_common_importer_utilities_GetConfig
{
    public $filename;

    function getConfig()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <root>
            <!-- SEZIONE EAD3 COMPLESSI E UNITÀ -->
            <ead3>
                <mapping>
                    <bibliografia_tipologiaSpecifica>libro</bibliografia_tipologiaSpecifica>
                    <bibliografia_annoDiEdizione>0</bibliografia_annoDiEdizione>
                </mapping>
                <vocabulary>
                    <!-- In "value" sono riportati i valori nei tracciati standard (ead3, scons2, eac-cpf) -->
                    <livelloDescrizione>
                        <!-- <entry value="cartografiastorica">unita</entry> -->
                    </livelloDescrizione>
                    <compilazione_azione>
                        <entry value="cancellazione">Cancellazione logica</entry>
                        <entry value="modifica">Integrazione successiva</entry>
                        <entry value="inserimento">Prima redazione</entry>
                    </compilazione_azione>
                    <tracciatiSpecifici>
                        <entry value="pergamena">Pergamena</entry>
                        <entry value="pergamene">Pergamena</entry>
                        <entry value="imago">Audiovisivo</entry>
                        <entry value="disegni">Grafica</entry>
                        <entry value="iconografica">Grafica</entry>
                        <entry value="fotografia">Fotografia</entry>
                    </tracciatiSpecifici>
                    <linguaEScrittura>
                        <!-- Il vocabolario riporta tutte le possibili combinazioni di voci di primo livello e secondo livello presenti in metaFAD, separate da "##". Decommentare le voci rilevanti per l\'importazione. -->
                        <entry value="Barocca Otrantina">Greco bizantino##Barocca Otrantina</entry>
                        <!--<entry value="Barocca Otrantina">Greco, Antico (fino al 1453)##Barocca Otrantina</entry>-->
                        <!--<entry value="Barocca Otrantina">Greco, Moderno (dal 1453 in poi)##Barocca Otrantina</entry>-->
                        <!--<entry value="Bastarda cancelleresca italiana">Latino##Bastarda cancelleresca italiana</entry>-->
                        <!--<entry value="Bastarda cancelleresca italiana">Volgare##Bastarda cancelleresca italiana</entry>-->
                        <!--<entry value="Beneventana">Latino##Beneventana</entry>-->
                        <!--<entry value="Beneventana">Volgare##Beneventana</entry>-->
                        <!--<entry value="Beneventana tipo Cassinese">Latino##Beneventana tipo Cassinese</entry>-->
                        <!--<entry value="Beneventana tipo Cassinese">Volgare##Beneventana tipo Cassinese</entry>-->
                        <!--<entry value="Beneventana tipo di Bari">Latino##Beneventana tipo di Bari</entry>-->
                        <!--<entry value="Beneventana tipo di Bari">Volgare##Beneventana tipo di Bari</entry>-->
                        <!--<entry value="Corsiva beneventana">Latino##Corsiva beneventana</entry>-->
                        <!--<entry value="Corsiva beneventana">Volgare##Corsiva beneventana</entry>-->
                        <!--<entry value="Corsiva nuova italiana">Latino##Corsiva nuova italiana</entry>-->
                        <!--<entry value="Corsiva nuova italiana">Volgare##Corsiva nuova italiana</entry>-->
                        <!--<entry value="Curiale">Latino##Curiale</entry>-->
                        <!--<entry value="Curiale">Volgare##Curiale</entry>-->
                        <!--<entry value="Curiale romana">Latino##Curiale romana</entry>-->
                        <!--<entry value="Curiale romana">Volgare##Curiale romana</entry>-->
                        <!--<entry value="Curialesca">Latino##Curialesca</entry>-->
                        <!--<entry value="Curialesca">Volgare##Curialesca</entry>-->
                        <!--<entry value="Druckminuskel">Greco bizantino##Druckminuskel</entry>-->
                        <!--<entry value="Druckminuskel">Greco, Antico (fino al 1453)##Druckminuskel</entry>-->
                        <!--<entry value="Druckminuskel">Greco, Moderno (dal 1453 in poi)##Druckminuskel</entry>-->
                        <!--<entry value="Fettaugen-Mode">Greco bizantino##Fettaugen-Mode</entry>-->
                        <!--<entry value="Fettaugen-Mode">Greco, Antico (fino al 1453)##Fettaugen-Mode</entry>-->
                        <!--<entry value="Fettaugen-Mode">Greco, Moderno (dal 1453 in poi)##Fettaugen-Mode</entry>-->
                        <!--<entry value="Littera textualis Bononiensis">Latino##Littera textualis Bononiensis</entry>-->
                        <!--<entry value="Littera textualis Bononiensis">Volgare##Littera textualis Bononiensis</entry>-->
                        <!--<entry value="Littera textualis Parisiensis">Latino##Littera textualis Parisiensis</entry>-->
                        <!--<entry value="Littera textualis Parisiensis">Volgare##Littera textualis Parisiensis</entry>-->
                        <!--<entry value="Maiuscola distintiva Alessandrina">Greco bizantino##Maiuscola distintiva Alessandrina</entry>-->
                        <!--<entry value="Maiuscola distintiva Alessandrina">Greco, Antico (fino al 1453)##Maiuscola distintiva Alessandrina</entry>-->
                        <!--<entry value="Maiuscola distintiva Alessandrina">Greco, Moderno (dal 1453 in poi)##Maiuscola distintiva Alessandrina</entry>-->
                        <!--<entry value="Maiuscola distintiva Costantinopolitana">Greco bizantino##Maiuscola distintiva Costantinopolitana</entry>-->
                        <!--<entry value="Maiuscola distintiva Costantinopolitana">Greco, Antico (fino al 1453)##Maiuscola distintiva Costantinopolitana</entry>-->
                        <!--<entry value="Maiuscola distintiva Costantinopolitana">Greco, Moderno (dal 1453 in poi)##Maiuscola distintiva Costantinopolitana</entry>-->
                        <!--<entry value="Maiuscola distintiva epigrafica">Greco bizantino##Maiuscola distintiva epigrafica</entry>-->
                        <!--<entry value="Maiuscola distintiva epigrafica">Greco, Antico (fino al 1453)##Maiuscola distintiva epigrafica</entry>-->
                        <!--<entry value="Maiuscola distintiva epigrafica">Greco, Moderno (dal 1453 in poi)##Maiuscola distintiva epigrafica</entry>-->
                        <!--<entry value="Maiuscola gotica">Latino##Maiuscola gotica</entry>-->
                        <!--<entry value="Maiuscola gotica">Volgare##Maiuscola gotica</entry>-->
                        <!--<entry value="Mercantesca">Latino##Mercantesca</entry>-->
                        <!--<entry value="Mercantesca">Volgare##Mercantesca</entry>-->
                        <!--<entry value="Metochitesstil">Greco bizantino##Metochitesstil</entry>-->
                        <!--<entry value="Metochitesstil">Greco, Antico (fino al 1453)##Metochitesstil</entry>-->
                        <!--<entry value="Metochitesstil">Greco, Moderno (dal 1453 in poi)##Metochitesstil</entry>-->
                        <!--<entry value="Minscuola bouletée">Greco bizantino##Minscuola bouletée</entry>-->
                        <!--<entry value="Minscuola bouletée">Greco, Antico (fino al 1453)##Minscuola bouletée</entry>-->
                        <!--<entry value="Minscuola bouletée">Greco, Moderno (dal 1453 in poi)##Minscuola bouletée</entry>-->
                        <!--<entry value="Minuscola ad asso di picche">Greco bizantino##Minuscola ad asso di picche</entry>-->
                        <!--<entry value="Minuscola ad asso di picche">Greco, Antico (fino al 1453)##Minuscola ad asso di picche</entry>-->
                        <!--<entry value="Minuscola ad asso di picche">Greco, Moderno (dal 1453 in poi)##Minuscola ad asso di picche</entry>-->
                        <!--<entry value="Minuscola agiopolita">Greco bizantino##Minuscola agiopolita</entry>-->
                        <!--<entry value="Minuscola agiopolita">Greco, Antico (fino al 1453)##Minuscola agiopolita</entry>-->
                        <!--<entry value="Minuscola agiopolita">Greco, Moderno (dal 1453 in poi)##Minuscola agiopolita</entry>-->
                        <!--<entry value="Minuscola antica oblunga (tipo Eustazio)">Greco bizantino##Minuscola antica oblunga (tipo Eustazio)</entry>-->
                        <!--<entry value="Minuscola antica oblunga (tipo Eustazio)">Greco, Antico (fino al 1453)##Minuscola antica oblunga (tipo Eustazio)</entry>-->
                        <!--<entry value="Minuscola antica oblunga (tipo Eustazio)">Greco, Moderno (dal 1453 in poi)##Minuscola antica oblunga (tipo Eustazio)</entry>-->
                        <!--<entry value="Minuscola antica rotonda (tipo Nicola)">Greco bizantino##Minuscola antica rotonda (tipo Nicola)</entry>-->
                        <!--<entry value="Minuscola antica rotonda (tipo Nicola)">Greco, Antico (fino al 1453)##Minuscola antica rotonda (tipo Nicola)</entry>-->
                        <!--<entry value="Minuscola antica rotonda (tipo Nicola)">Greco, Moderno (dal 1453 in poi)##Minuscola antica rotonda (tipo Nicola)</entry>-->
                        <!--<entry value="Minuscola cancelleresca (Littera minuta corsiva)">Latino##Minuscola cancelleresca (Littera minuta corsiva)</entry>-->
                        <!--<entry value="Minuscola cancelleresca (Littera minuta corsiva)">Pergamena##Minuscola cancelleresca (Littera minuta corsiva)</entry>-->
                        <!--<entry value="Minuscola cancelleresca all’antica">Latino##Minuscola cancelleresca all’antica</entry>-->
                        <!--<entry value="Minuscola cancelleresca all’antica">Volgare##Minuscola cancelleresca all’antica</entry>-->
                        <!--<entry value="Minuscola cancelleresca italica">Latino##Minuscola cancelleresca italica</entry>-->
                        <!--<entry value="Minuscola cancelleresca italica">Volgare##Minuscola cancelleresca italica</entry>-->
                        <!--<entry value="Minuscola carolina">Latino##Minuscola carolina</entry>-->
                        <!--<entry value="Minuscola carolina">Volgare##Minuscola carolina</entry>-->
                        <!--<entry value="Minuscola corsiva (corsiva nuova)">Latino##Minuscola corsiva (corsiva nuova)</entry>-->
                        <!--<entry value="Minuscola corsiva (corsiva nuova)">Volgare##Minuscola corsiva (corsiva nuova)</entry>-->
                        <!--<entry value="Minuscola corsiveggiante">Greco bizantino##Minuscola corsiveggiante</entry>-->
                        <!--<entry value="Minuscola corsiveggiante">Greco, Antico (fino al 1453)##Minuscola corsiveggiante</entry>-->
                        <!--<entry value="Minuscola corsiveggiante">Greco, Moderno (dal 1453 in poi)##Minuscola corsiveggiante</entry>-->
                        <!--<entry value="Minuscola del copista del Metafrasta">Greco bizantino##Minuscola del copista del Metafrasta</entry>-->
                        <!--<entry value="Minuscola del copista del Metafrasta">Greco, Antico (fino al 1453)##Minuscola del copista del Metafrasta</entry>-->
                        <!--<entry value="Minuscola del copista del Metafrasta">Greco, Moderno (dal 1453 in poi)##Minuscola del copista del Metafrasta</entry>-->
                        <!--<entry value="Minuscola della collezione filosofica">Greco bizantino##Minuscola della collezione filosofica</entry>-->
                        <!--<entry value="Minuscola della collezione filosofica">Greco, Antico (fino al 1453)##Minuscola della collezione filosofica</entry>-->
                        <!--<entry value="Minuscola della collezione filosofica">Greco, Moderno (dal 1453 in poi)##Minuscola della collezione filosofica</entry>-->
                        <!--<entry value="Minuscola di transizione">Latino##Minuscola di transizione</entry>-->
                        <!--<entry value="Minuscola di transizione">Volgare##Minuscola di transizione</entry>-->
                        <!--<entry value="Minuscola diplomatica">Latino##Minuscola diplomatica</entry>-->
                        <!--<entry value="Minuscola diplomatica">Volgare##Minuscola diplomatica</entry>-->
                        <!--<entry value="Minuscola gotica / Littera textualis">Latino##Minuscola gotica / Littera textualis</entry>-->
                        <!--<entry value="Minuscola gotica / Littera textualis">Volgare##Minuscola gotica / Littera textualis</entry>-->
                        <!--<entry value="Minuscola gotica rotunda">Latino##Minuscola gotica rotunda</entry>-->
                        <!--<entry value="Minuscola gotica rotunda">Volgare##Minuscola gotica rotunda</entry>-->
                        <!--<entry value="Minuscola informale">Greco bizantino##Minuscola informale</entry>-->
                        <!--<entry value="Minuscola informale">Greco, Antico (fino al 1453)##Minuscola informale</entry>-->
                        <!--<entry value="Minuscola informale">Greco, Moderno (dal 1453 in poi)##Minuscola informale</entry>-->
                        <!--<entry value="Minuscola insulare">Latino##Minuscola insulare</entry>-->
                        <!--<entry value="Minuscola insulare">Volgare##Minuscola insulare</entry>-->
                        <!--<entry value="Minuscola italica testeggiata">Latino##Minuscola italica testeggiata</entry>-->
                        <!--<entry value="Minuscola italica testeggiata">Volgare##Minuscola italica testeggiata</entry>-->
                        <!--<entry value="Minuscola merovingica">Latino##Minuscola merovingica</entry>-->
                        <!--<entry value="Minuscola merovingica">Volgare##Minuscola merovingica</entry>-->
                        <!--<entry value="Minuscola niliana">Greco bizantino##Minuscola niliana</entry>-->
                        <!--<entry value="Minuscola niliana">Greco, Antico (fino al 1453)##Minuscola niliana</entry>-->
                        <!--<entry value="Minuscola niliana">Greco, Moderno (dal 1453 in poi)##Minuscola niliana</entry>-->
                        <!--<entry value="Minuscola romanesca">Latino##Minuscola romanesca</entry>-->
                        <!--<entry value="Minuscola romanesca">Volgare##Minuscola romanesca</entry>-->
                        <!--<entry value="Minuscola umanistica (Littera antiqua)">Latino##Minuscola umanistica (Littera antiqua)</entry>-->
                        <!--<entry value="Minuscola umanistica (Littera antiqua)">Volgare##Minuscola umanistica (Littera antiqua)</entry>-->
                        <!--<entry value="Minuscola umanistica corsiva">Latino##Minuscola umanistica corsiva</entry>-->
                        <!--<entry value="Minuscola umanistica corsiva">Volgare##Minuscola umanistica corsiva</entry>-->
                        <!--<entry value="Minuscola visigotica">Latino##Minuscola visigotica</entry>-->
                        <!--<entry value="Minuscola visigotica">Volgare##Minuscola visigotica</entry>-->
                        <!--<entry value="Minuscole altomedievali">Latino##Minuscole altomedievali</entry>-->
                        <!--<entry value="Minuscole altomedievali">Volgare##Minuscole altomedievali</entry>-->
                        <!--<entry value="Nuova corsiva">Latino##Nuova corsiva</entry>-->
                        <!--<entry value="Nuova corsiva">Volgare##Nuova corsiva</entry>-->
                        <!--<entry value="Onciale">Latino##Onciale</entry>-->
                        <!--<entry value="Onciale">Volgare##Onciale</entry>-->
                        <!--<entry value="Onciale Romana">Latino##Onciale Romana</entry>-->
                        <!--<entry value="Onciale Romana">Volgare##Onciale Romana</entry>-->
                        <!--<entry value="Perlschrift">Greco bizantino##Perlschrift</entry>-->
                        <!--<entry value="Perlschrift">Greco, Antico (fino al 1453)##Perlschrift</entry>-->
                        <!--<entry value="Perlschrift">Greco, Moderno (dal 1453 in poi)##Perlschrift</entry>-->
                        <!--<entry value="Pre-antiqua">Latino##Pre-antiqua</entry>-->
                        <!--<entry value="Pre-antiqua">Volgare##Pre-antiqua</entry>-->
                        <!--<entry value="Reservatschrift">Greco bizantino##Reservatschrift</entry>-->
                        <!--<entry value="Reservatschrift">Greco, Antico (fino al 1453)##Reservatschrift</entry>-->
                        <!--<entry value="Reservatschrift">Greco, Moderno (dal 1453 in poi)##Reservatschrift</entry>-->
                        <!--<entry value="Scrittura individuale">Greco bizantino##Scrittura individuale</entry>-->
                        <!--<entry value="Scrittura individuale">Greco, Antico (fino al 1453)##Scrittura individuale</entry>-->
                        <!--<entry value="Scrittura individuale">Greco, Moderno (dal 1453 in poi)##Scrittura individuale</entry>-->
                        <!--<entry value="Scritture arcaizzanti (mimesi grafica)">Greco bizantino##Scritture arcaizzanti (mimesi grafica)</entry>-->
                        <!--<entry value="Scritture arcaizzanti (mimesi grafica)">Greco, Antico (fino al 1453)##Scritture arcaizzanti (mimesi grafica)</entry>-->
                        <!--<entry value="Scritture arcaizzanti (mimesi grafica)">Greco, Moderno (dal 1453 in poi)##Scritture arcaizzanti (mimesi grafica)</entry>-->
                        <!--<entry value="Scritture crisolorine">Greco bizantino##Scritture crisolorine</entry>-->
                        <!--<entry value="Scritture crisolorine">Greco, Antico (fino al 1453)##Scritture crisolorine</entry>-->
                        <!--<entry value="Scritture crisolorine">Greco, Moderno (dal 1453 in poi)##Scritture crisolorine</entry>-->
                        <!--<entry value="Semigotica">Latino##Semigotica</entry>-->
                        <!--<entry value="Semigotica">Volgare##Semigotica</entry>-->
                        <!--<entry value="Semigotica delle carte / Semigotica corsiva">Latino##Semigotica delle carte / Semigotica corsiva</entry>-->
                        <!--<entry value="Semigotica delle carte / Semigotica corsiva">Volgare##Semigotica delle carte / Semigotica corsiva</entry>-->
                        <!--<entry value="Semionciale">Latino##Semionciale</entry>-->
                        <!--<entry value="Semionciale">Volgare##Semionciale</entry>-->
                        <!--<entry value="Stile di Reggio">Greco bizantino##Stile di Reggio</entry>-->
                        <!--<entry value="Stile di Reggio">Greco, Antico (fino al 1453)##Stile di Reggio</entry>-->
                        <!--<entry value="Stile di Reggio">Greco, Moderno (dal 1453 in poi)##Stile di Reggio</entry>-->
                        <!--<entry value="Stile otrantino / Stile rettangolare appiattito">Greco bizantino##Stile otrantino / Stile rettangolare appiattito</entry>-->
                        <!--<entry value="Stile otrantino / Stile rettangolare appiattito">Greco, Antico (fino al 1453)##Stile otrantino / Stile rettangolare appiattito</entry>-->
                        <!--<entry value="Stile otrantino / Stile rettangolare appiattito">Greco, Moderno (dal 1453 in poi)##Stile otrantino / Stile rettangolare appiattito</entry>-->
                        <!--<entry value="Stile τῶν Ὁδηγῶν">Greco bizantino##Stile τῶν Ὁδηγῶν</entry>-->
                        <!--<entry value="Stile τῶν Ὁδηγῶν">Greco, Antico (fino al 1453)##Stile τῶν Ὁδηγῶν</entry>-->
                        <!--<entry value="Stile τῶν Ὁδηγῶν">Greco, Moderno (dal 1453 in poi)##Stile τῶν Ὁδηγῶν</entry>-->
                        <!--<entry value="Tipo Anastasio">Greco bizantino##Tipo Anastasio</entry>-->
                        <!--<entry value="Tipo Anastasio">Greco, Antico (fino al 1453)##Tipo Anastasio</entry>-->
                        <!--<entry value="Tipo Anastasio">Greco, Moderno (dal 1453 in poi)##Tipo Anastasio</entry>-->
                    </linguaEScrittura>
                    <validita>
                        <entry value="data-ante-quem">Data ante quem</entry>
                        <entry value="data-approssimativa">Data approssimativa</entry>
                        <entry value="data-attribuita">Data attribuita</entry>
                        <entry value="data-incerta">Data incerta</entry>
                        <entry value="data-post-quem">Data post quem</entry>
                    </validita>
                    <materialeSigillo>
                        <entry value="argento">Argento</entry>
                        <entry value="argilla">Argilla</entry>
                        <entry value="cera">Cera</entry>
                        <entry value="ceralacca">Ceralacca</entry>
                        <entry value="oro">Oro</entry>
                        <entry value="piombo">Piombo</entry>
                    </materialeSigillo>
                    <stadioDocumento>
                        <entry value="copia">Copia</entry>
                        <entry value="copia autentica">Copia autentica</entry>
                        <entry value="copia coeva">Copia coeva</entry>
                        <entry value="copia semplice">Copia semplice</entry>
                        <entry value="copia tarda">Copia tarda</entry>
                        <entry value="minuta">Minuta</entry>
                        <entry value="originale">Originale</entry>
                    </stadioDocumento>
                    <autore_ruolo>
                        <entry value="autore">Autore</entry>
                        <entry value="destinatario">Destinatario</entry>
                        <entry value="disegnatore">Disegnatore</entry>
                        <entry value="editore">Editore</entry>
                        <entry value="firmatario">Firmatario</entry>
                        <entry value="giudice">Giudice</entry>
                        <entry value="incisore">Incisore</entry>
                        <entry value="mittente">Mittente</entry>
                        <entry value="notaio">Notaio</entry>
                        <entry value="relazione">Relazione</entry>
                        <entry value="scrittore/rogatario">Scrittore/Rogatario</entry>
                        <entry value="testimone">Testimone</entry>
                    </autore_ruolo>
                    <supporto>
				        <entry value="cartaceo">Cartaceo</entry>
				        <entry value="digitale">Digitale</entry>
				        <entry value="membramenaceo">Membranaceo</entry>
				        <entry value="misto">Misto</entry>
				        <entry value="papiraceo">Papiraceo</entry>
				        <entry value="pergamenaceo">Membranaceo</entry>
			        </supporto>
                </vocabulary>
            </ead3>
        
            <!-- SEZIONE EAD3 STRUMENTI DI RICERCA -->
            <ead3_strumenti>
                <mapping>
                    <bibliografia_tipologiaSpecifica>libro</bibliografia_tipologiaSpecifica>
                    <bibliografia_annoDiEdizione>0</bibliografia_annoDiEdizione>
                </mapping>
                <vocabulary>
                    <tipologia_tipologia>
                        <entry value="banca dati">Banca dati</entry>
                        <entry value="censimento">Censimento</entry>
                        <entry value="elenco di consistenza">Elenco di consistenza</entry>
                        <entry value="elenco di deposito">Elenco di deposito</entry>
                        <entry value="elenco di versamento">Elenco di versamento</entry>
                        <entry value="guida">Guida</entry>
                        <entry value="indice">Indice</entry>
                        <entry value="inventario">Inventario</entry>
                        <entry value="inventario analitico">Inventario analitico</entry>
                        <entry value="regesto">Regesto</entry>
                        <entry value="repertorio alfabetico">Repertorio alfabetico</entry>
                        <entry value="repertorio cronologico">Repertorio cronologico</entry>
                        <entry value="spoglio">Spoglio</entry>
                        <entry value="altro">Altro</entry>
                    </tipologia_tipologia>
                    <descrizioneEstrinseca_tipoSupporto>
                        <entry value="analogico/cartaceo">Analogico/cartaceo</entry>
                        <entry value="cartaceo">Analogico/cartaceo</entry>
                        <entry value="analogico">Analogico/cartaceo</entry>
                        <entry value="digitale">Digitale</entry>
                        <entry value="non rilevato">Non rilevato</entry>
                    </descrizioneEstrinseca_tipoSupporto>
                    <modalitaDiRedazione_edito>
                        <entry value="sì">Sì</entry>
                        <entry value="no">No</entry>
                    </modalitaDiRedazione_edito>
                    <modalitaDiRedazione_tipologia>
                        <entry value="dattiloscritto">Dattiloscritto</entry>
                        <entry value="manoscritto">Manoscritto</entry>
                        <entry value="stampa da computer">Stampa da computer</entry>
                        <entry value="altro">Altro</entry>
                    </modalitaDiRedazione_tipologia>
                    <compilazione_azione>
                        <entry value="cancelled">Cancellazione logica</entry>
                        <entry value="updated">Integrazione successiva</entry>
                        <entry value="created">Prima redazione</entry>
                        <entry value="revised">Revisione</entry>
                        <entry value="derived">Importazione</entry>
                    </compilazione_azione>
                    <compilazione_tipologiaRedattore>
                        <entry value="human">Persona</entry>
                        <entry value="machine">Software</entry>
                    </compilazione_tipologiaRedattore>
                    <validita>
                        <entry value="data-ante-quem">Data ante quem</entry>
                        <entry value="data-approssimativa">Data approssimativa</entry>
                        <entry value="data-attribuita">Data attribuita</entry>
                        <entry value="data-incerta">Data incerta</entry>
                        <entry value="data-post-quem">Data post quem</entry>
                    </validita>
                </vocabulary>
            </ead3_strumenti>
        
            <!-- SEZIONE SCONS2 SOGGETTI CONSERVATORI -->
            <scons2>
                <mapping>
                    <bibliografia_tipologiaSpecifica>libro</bibliografia_tipologiaSpecifica>
                    <bibliografia_annoDiEdizione>0</bibliografia_annoDiEdizione>
                </mapping>
                <vocabulary>
                    <compilazione_azione>
                        <entry value="cancellazione">Cancellazione logica</entry>
                        <entry value="modifica">Integrazione successiva</entry>
                        <entry value="creazione">Prima redazione</entry>
                    </compilazione_azione>
                    <denominazione_qualificaDellaDenominazione>
                        <entry value="principale">Denominazione principale</entry>
                        <entry value="altraDenominazione">Altre denominazioni</entry>
                    </denominazione_qualificaDellaDenominazione>
                    <tipologia_tipologiaEnte>
                        <entry value="tesaurosan/ente_di_cultura-ente_di_ricerca">Accademia/Istituto/Ente di cultura</entry>
                        <entry value="tesaurosan/associazione_civile_e_di_movimento_conservatore">Associazione civile/di movimento</entry>
                        <entry value="tesaurosan/associazione_combattentistica_e_d_arma_conservatore">Associazione combattentistica e d’arma</entry>
                        <entry value="tesaurosan/istituto_di_credito">Banca/Ente di credito e finanziario</entry>
                        <entry value="tesaurosan/comune-citta_metropolitana-unione_di_comuni_organo _e_ufficio_conservatore">Comune/città metropolitana/unione di comuni (organo e/o ufficio)</entry>
                        <entry value="tesaurosan/organo_e_ufficio_statale_centrale_del_periodo_postunitario">Corpo militare</entry>
                        <entry value="tesaurosan/ente_assicurativo">Ente assicurativo e previdenziale</entry>
                        <entry value="tesaurosan/ente_di_assistenza-beneficenza-previdenza-servizi_alla_persona">Ente di assistenza e beneficenza</entry>
                        <entry value="tesaurosan/ente_di_gestione_di_acque_ambiente_beni_indivisi_energia_territorio_trasporti">Ente e Azienda di servizi territoriali (acque, ambiente, energia, trasporti)</entry>
                        <entry value="tesaurosan/ente_e_associazione_di_culto_cattolico">Ente e istituzione della Chiesa cattolica</entry>
                        <entry value="tesaurosan/ente_e_associazione_di_culti_acattolici">Ente e istituzione di confessioni religiose non cattoliche</entry>
                        <entry value="tesaurosan/ente_diverso">Ente funzionale territoriale</entry>
                        <entry value="tesaurosan/ente_territoriale">Ente pubblico territoriale</entry>
                        <entry value="tesaurosan/ente_ricreativo-sportivo-turistico_conservatore">Ente/Associazione ricreativa</entry>
                        <entry value="tesaurosan/ente_economico_e_di_promozione_economica-impresa-studio_professionale_conservatore">Impresa</entry>
                        <entry value="tesaurosan/studio_notarile-istituto_notarile-notaio">Notaio/studio notarile/istituto notarile</entry>
                        <entry value="tesaurosan/arte-ordine-collegio-associazione_di_categoria_conservatore">Ordine professionale, Associazione di categoria</entry>
                        <entry value="tesaurosan/organo_e_ufficio_statale_centrale_del_periodo_postunitario">Organo/ufficio centrale dello stato unitario</entry>
                        <entry value="tesaurosan/organo_e_ufficio_statale_periferico_del_periodo_postunitario">Organo/ufficio periferico dello stato unitario</entry>
                        <entry value="tesaurosan/ente_sanitario">Ospedale/ente sanitario</entry>
                        <entry value="tesaurosan/partito_e_movimento_politico-associazione_politica_conservatore">Partito e movimento politico/associazione politica</entry>
                        <entry value="tesaurosan/provincia-provincia_autonoma_organo_e_ufficio_conservatore">Provincia/provincia autonoma (organo e/o ufficio)</entry>
                        <entry value="tesaurosan/regione-regione_a_statuto_speciale_organo_e_ufficio_conservatore">Regione/regione a statuto speciale</entry>
                        <entry value="tesaurosan/ente_di_istruzione">Scuola/ente di istruzione, ente di formazione</entry>
                        <entry value="tesaurosan/sindacato-organizzazione_sindacale_conservatore">Sindacato/organizzazione sindacale</entry>
                        <entry value="tesaurosan/ente_di_cultura-ente_di_ricerca">Università/ente di ricerca</entry>
                    </tipologia_tipologiaEnte>
                    <soggettiConservatori_tipoRelazione>
                        <entry value="associativa">Associativa</entry>
                        <entry value="gerarchica">Gerarchica</entry>
                        <entry value="gerarchicafiglio">GerarchicaFiglio</entry>
                        <entry value="gerarchicapadre">GerarchicaPadre</entry>
                        <entry value="identità">Identità</entry>
                        <entry value="temporale">Temporale</entry>
                        <entry value="temporaleprecedente">TemporalePrecedente</entry>
                        <entry value="temporalesuccessiva">TemporaleSuccessiva</entry>
                    </soggettiConservatori_tipoRelazione>
                </vocabulary>
            </scons2>
        
            <!-- SEZIONE EAC-CPF SOGGETTI PRODUTTORI -->
            <eac-cpf>
                <mapping>
                    <bibliografia_tipologiaSpecifica>libro</bibliografia_tipologiaSpecifica>
                    <bibliografia_annoDiEdizione>0</bibliografia_annoDiEdizione>
                    <condizioneGiuridica_condizioneGiuridica>Ente pubblico</condizioneGiuridica_condizioneGiuridica>
                </mapping>
                <vocabulary>
                    <compilazione_azione>
                        <entry value="cancelled">Cancellazione logica</entry>
                        <entry value="updated">Integrazione successiva</entry>
                        <entry value="created">Prima redazione</entry>
                        <entry value="revised">Revisione</entry>
                        <entry value="derived">Importazione</entry>
                    </compilazione_azione>
                    <compilazione_tipologiaRedattore>
                        <entry value="human">Persona</entry>
                        <entry value="machine">Software</entry>
                    </compilazione_tipologiaRedattore>
                    <tipologia_tipologia>
                        <entry value="corporatebody">Ente</entry>
                        <entry value="family">Famiglia</entry>
                        <entry value="person">Persona</entry>
                    </tipologia_tipologia>
                    <denominazione_qualificaDellaDenominazione>
                           <entry value="altraDenominazione">Altre denominazioni</entry>
                    </denominazione_qualificaDellaDenominazione>
                    <ente_luogo_qualificaLuogo>
                        <entry value="tesaurosan/giurisdizione">Giurisdizione</entry>
                        <entry value="tesaurosan/sede">Sede</entry>
                    </ente_luogo_qualificaLuogo>
                    <famiglia_luogo_qualificaLuogo>
                        <entry value="tesaurosan/giurisdizione">Sede di attività</entry>
                        <entry value="tesaurosan/domicilio">Sede</entry>
                        <entry value="tesaurosan/residenza">Residenza</entry>
                    </famiglia_luogo_qualificaLuogo>
                    <persona_luogo_qualificaLuogo>
                        <entry value="tesaurosan/luogo_di_morte">Morte</entry>
                        <entry value="tesaurosan/luogo_di_nascita">Nascita</entry>
                        <entry value="tesaurosan/residenza">Residenza</entry>
                        <entry value="tesaurosan/domicilio">Sede</entry>
                        <entry value="tesaurosan/giurisdizione">Sede di attività</entry>
                    </persona_luogo_qualificaLuogo>
                    <condizioneGiuridica_condizioneGiuridica>
                        <entry value="ente di culto">Ente di culto</entry>
                        <entry value="ente privato">Ente privato</entry>
                        <entry value="ente pubblico">Ente pubblico</entry>
                    </condizioneGiuridica_condizioneGiuridica>
                    <tipologia_tipologiaEnte>
                        <entry value="tesaurosan/accademia-ente_di_cultura">Accademia/Istituto/Ente di cultura</entry>
                        <entry value="tesaurosan/associazione_civile_e_di_movimento_produttore">Associazione civile/di movimento</entry>
                        <entry value="tesaurosan/associazione_combattentistica_e_d_arma_produttore">Associazione combattentistica e d’arma</entry>
                        <entry value="tesaurosan/banca-istituto_di_credito-ente_assicurativo-ente_previdenziale">Banca/Ente di credito e finanziario</entry>
        <entry value="tesaurosan/comitato_di_liberazione_nazionale-corpo_militare_della_resistenza">Comitato di liberazione nazionale</entry>
                        <entry value="tesaurosan/comune-citta_metropolitana-unione_di_comuni_organo _e_ufficio_produttore">Comune/città metropolitana/unione di comuni (organo e/o ufficio)</entry>
                        <entry value="tesaurosan/organo_o_ufficio_statale_del_periodo_postunitario">Corpo militare</entry>
                        <entry value="tesaurosan/ente_assicurativo">Ente assicurativo e previdenziale</entry>
                        <entry value="tesaurosan/opera_pia-istituzione_ed_ente_di_assistenza_e_beneficenza-ospedale">Ente di assistenza e beneficenza</entry>
                        <entry value="tesaurosan/ente_di_gestione_di_acque_ambiente_energia_territorio_trasporti">Ente e Azienda di servizi territoriali (acque, ambiente, energia, trasporti)</entry>
                        <entry value="tesaurosan/ente_di_culto_cattolico-associazione_cattolica">Ente e istituzione della Chiesa cattolica</entry>
                        <entry value="tesaurosan/ente_di_culto_acattolico-associazione_acattolica">Ente e istituzione di confessioni religiose non cattoliche</entry>
                        <entry value="tesaurosan/ente_economico_e_di_promozione_economica-impresa-studio_professionale_produttore">Ente economico</entry>
                        <entry value="tesaurosan/ente_diverso">Ente funzionale territoriale</entry>
                        <entry value="tesaurosan/ente_territoriale_minore">Ente pubblico territoriale</entry>
                        <entry value="tesaurosan/ente_ricreativo-sportivo-turistico_produttore">Ente/Associazione ricreativa</entry>
                        <entry value="tesaurosan/ente_economico_e_di_promozione_economica-impresa-studio_professionale_produttore">Impresa</entry>
                        <entry value="tesaurosan/organo_centrale_di_stato_di_antico_regime">Magistratura e/o ufficio di Antico regime</entry>
                        <entry value="tesauroSAN/organo_periferico_o_locale_di_stato_di_antico_regime">Magistratura e/o ufficio di Antico regime</entry>
                        <entry value="tesaurosan/notaio">Notaio/studio notarile/istituto notarile</entry>
                        <entry value="tesaurosan/organo_centrale_di_stato_del_periodo_napoleonico">Organo e/o ufficio statale del periodo napoleonico</entry>
                        <entry value="tesaurosan/organo_periferico_di_stato_del_periodo_napoleonico">Organo e/o ufficio statale del periodo napoleonico</entry>
                        <entry value="tesaurosan/organo_centrale_di_stato_della_restaurazione">Organo e/o ufficio statale della Restaurazione</entry>
                        <entry value="tesaurosan/organo_periferico_di_stato_della_restaurazione">Organo e/o ufficio statale della Restaurazione</entry>
                        <entry value="tesaurosan/arte-ordine-collegio-associazione_di_categoria_produttore">Ordine professionale, Associazione di categoria</entry>
                        <entry value="tesaurosan/organo_e_ufficio_statale_centrale_di_periodo_postunitario">Organo/ufficio centrale dello stato unitario</entry>
                        <entry value="tesaurosan/organo_e_ufficio_statale_periferico_di_periodo_postunitario">Organo/ufficio periferico dello stato unitario</entry>
                        <entry value="tesaurosan/ente_sanitario-ente_di_servizi_alla_persona">Ospedale/ente sanitario</entry>
                        <entry value="tesaurosan/partito_e_movimento_politico-associazione_politica_produttore">Partito e movimento politico/associazione politica</entry>
                        <entry value="tesaurosan/provincia-provincia_autonoma_organo_e_ufficio_produttore">Provincia/provincia autonoma (organo e/o ufficio)</entry>
                        <entry value="tesaurosan/regione-regione_a_statuto_speciale_organo_e_ufficio_produttore">Regione/regione a statuto speciale</entry>
                        <entry value="tesaurosan/scuola-ente_di_istruzione">Scuola/ente di istruzione, ente di formazione</entry>
                        <entry value="tesaurosan/sindacato-organizzazione_sindacale_produttore">Sindacato/organizzazione sindacale</entry>
                        <entry value="tesaurosan/università-ente_di_ricerca">Università/ente di ricerca</entry>
                    </tipologia_tipologiaEnte>
                    <luogoFamiglia_qualificaLuogo>
                        <entry value="domicilio">sede</entry>
                        <entry value="residenza">residenza</entry>
                        <entry value="sede di attività">giurisdizione</entry>
                        <entry value="origine">#non mappato#</entry>
                    </luogoFamiglia_qualificaLuogo>
                    <luogoPersona_qualificaLuogo>
                        <entry value="domicilio">sede</entry>
                        <entry value="morte">luogo di morte</entry>
                        <entry value="nascita">luogo di nascita</entry>
                        <entry value="residenza">residenza</entry>
                        <entry value="sede di attività">giurisdizione</entry>
                    </luogoPersona_qualificaLuogo>
                </vocabulary>
            </eac-cpf>
        </root>';
    }
}
