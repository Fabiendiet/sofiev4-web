
<pageheader name="PdfHeader" content-left="Minitère de l'Equipement Rural" content-center="" line="on"
            content-right="SOFIE"
            header-style="font-family: serif; font-size: 10pt; font-weight: bold; color: #000000;" />

<pagefooter name="PdfFooter" content-right="{PAGENO}/{nbpg}" line="on"
            footer-style="font-family: serif; font-size: 8pt; font-weight: bold; font-style: italic; color: #000000;" />

<setpageheader name="PdfHeader" value="on" show-this-page="1" />
<setpagefooter name="PdfFooter" value="on" />

<table style="width: 100%">
    <tr>
        <td width="88%">
            <div>
                <strong>Date</strong> : {{ "now"|date('d/m/Y H:i:s') }}
            </div>
            {% if(g_sofie_site|default(null) is not null and g_region_infos|default(null) is iterable) %}
            <div>
                <strong>Site</strong> : {{ g_region_infos['name']|default('n/a') }}
            </div>
            {% endif %}
            <div>
                <strong>Liste de données</strong> : {% block data_source %}{% endblock %}
            </div>
            <div>
                <strong>Nombre de lignes</strong> : {% block data_length %}{% endblock %}
            </div>
            <div>
                <strong>Critères</strong> :
                <span id="criteres">{{ (criteres|default('') is empty) ? 'n/a' : criteres|default('') }}</span>
            </div>
        </td>
        <td>
            <img src="{{ asset('img/armoirie.jpg') }}" style="height: 68px; width: 75px;" />
        </td>
    </tr>
</table>

<table class="pdftable pdftable-striped pdftable-heading pdftable-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Sécrétaire</th>
            <th>Initialisé</th>
            <th>Code d'initialisation</th>
            <th>Numéro d'appel</th>
            <th>Région</th>
            <th>Localité</th>
        </tr>
    </thead>
    {% if comites is defined %}
        {% if comites is not empty %}
            <tbody>
                {% set page_inc = g_paginator_items|default(0)*(page|default(1)-1) %}
                    {% for comite in comites %}
                    <tr>
                        <td>{{ loop.index+page_inc }}</td>
                        <td>{{ comite.nom|default('') }}</td>
                        <td>{{ comite.nomSecretaire|default('')~' '~comite.prenomsSecretaire|default('') }}</td>
                        <td>{{ comite.initStatus|default(false)|oui_non }}</td>
                        <td>{{ comite.codeInit|default('') }}</td>
                        <td>{{ comite.numeroAppel.numero|default('') }}</td>
                        <td>{{ comite.region.nom|default('') }}</td>
                        <td>{{ comite.localite.nom|default('') }}</td>
                    </tr>
                {% endfor %}
            </tbody>
        {% endif %}
    {% endif %}
</table>
