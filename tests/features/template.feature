Feature: Template
  In order to process a Template
  As a developer of PHP tools using Twig
  I need to be able analyze a given template

  Scenario: Simple template variable extract
    Given the template
      """
      Lorem ipsum {{ dolor }} sit {{ amet }}
      """
    Then I should get 2 variables
    And "dolor" is one variable name
    And "amet" is one variable name

  Scenario: Simple Block extract
    Given the template
      """
      The dummy text is
      {% block lorem %}
      Lorem ipsum dolor sit amet
      {% endblock %}

      and it is reusable:
      {{ block("lorem") }}
      """
    Then I should get 1 blocks

  Scenario: Simple Block extract
    Given the template
      """
      The dummy text is
      {% block lorem %}
      Lorem ipsum dolor sit amet
      {% endblock %}

      and it is reusable:
      {{ block("lorem") }}

      Another very usefull german dummy text is given by this block:
      {% block blabakus %}
      Das hier ist der nützlichste und klügste Blindtext der ganzen Welt, weil er dir genau sagt, wann 100 Zeichen vorbei sind (gleich nach der Zahl). Kaum begreift man das Prinzip, zählt der Text schon 200 Zeichen, inklusive Leerschläge. Damit hast du nun eine geniale Methode zur Hand, einen Text von 300 Zeichen Länge zu visualisieren. Mal angenommen, du willst sehen, welch ansprechenden Textkörper 400 Zeichen bilden können – jetzt hast du ein Mass dafür. Und nachdem dieser amüsante Blindtext mit 500 Zeichen die Hälfte erreicht hat, könnte man eine Betrachtung anstellen, die sich schon über die 600er Marke hinaus erstreckt: Haben wir es hier wirklich mit Blindtext zu tun, oder könnte man nach 700 präzis getexteten Zeichen nicht schon von Content sprechen? Mit dieser Frage überrollen wir die 800-Zeichen-Grenze und bedanken uns herzlich, dass du diesen vermeintlich blinden Text von mehr als 900 Zeichen Länge tatsächlich gelesen hast. Damit setzen wir den Schlusspunkt, und zwar hinter die 999.
      {% endblock %}
      """
    Then I should get 2 blocks

  Scenario: Nested Block extract with variables
    Given the template
      """
      <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="de">
        <head>
        {% block stylesheet %}
          <style type="text/css">
            {% block color %}
              p {
                color: {{ p_color }};
              }
            {% endblock %}
          </style>
        {% endblock %}
        </head>
        <body>
          {% block body %}
          <p>
            Lorem ipsum {{ dolor | default('bla') }} sit {{ amet }}
          </p>
          {% endblock %}
        </body>
        {{ block('stylesheet') }}
      </html>
      """
    Then I should get 3 blocks
    And I should get 3 variables
    And "dolor" is one variable name
    And "amet" is one variable name
    And "p_color" is one variable name
