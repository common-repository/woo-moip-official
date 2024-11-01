=== Pagamento Moip for WooCommerce ===

Contributors: daniloalvess, victorfreitas, ivaniltongomes, aguiart0, apiki
Tags: woocommerce, checkout, cart, wirecard, moip, gateway, payments, split
Requires at least: 4.0
Requires PHP: 7.1
Tested up to: 6.1.1
Stable tag: 1.4.7.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Official Moip Brazil plugin built with the best development practices.
Based on V2, new REST Moip’s API, providing more speed, safety and sales conversion.

== Description ==

Payment split NOW!

Official Moip Brazil plugin built with the best development practices.
Based on V2, new REST Moip API, providing more speed, safety and sales conversion.

= Requirements =

- PHP version 7.1 or greater.
- WooCommerce version 4.0.x or greater.
- Create sandbox account on Moip, [here](https://bem-vindo-sandbox.wirecard.com.br/ "Moip Sandbox").
- Create production account on Moip, [here](https://bem-vindo.wirecard.com.br/ "Moip Production").

= Payments methods =

- Credit card: Visa, Mastercard, Elo, Amex, Diners, Hiper e Hipercard.
- Itaú bank transfering.
- Billet banking.

= Benefits =

- Transparent/seamless checkout. No redirect to another page or pop up screen.
- All payment status are synchronized in the store admin panel.
- Boleto bar code showed on the checkout to facilitate the payment using internet banking.
- Developed based on the best practices from Wordpress and Woocommerce  to avoid Plugin incompatibility.
- Immediate cancellation status return on the checkout page so the consumer can instantly opt to change the payment method without leaving the cart.
- Registration form adapted to the Brazilian requirements. No need to install other plugins.
- Redirect checkout as an option.

= Payment Split =

- Support link: [Documentation](https://blog.apiki.com/e-commerce/)
- [Dokan](https://wordpress.org/plugins/dokan-lite/)
- [WCFM](https://wordpress.org/plugins/wc-multivendor-marketplace/)

= Support =

Free support followed by Moip integration and partnerships team.
Support link: [Support](https://apiki.com/parceiros/moip/)

= Transaction Fees =

Check out Moip’s site for transaction fees. If your store monthly volume is more than R$20.000,00, please contact us for a personalized proposal at parcerias@moip.com.br.

== Installation ==

1. Faça upload deste plugin em seu WordPress, e ative-o;
2. Entre no menu lateral "WooCommerce > Configurações > Pagamentos";
3. Na aba geral clica no botao "Autorizar", selecionando "Produção" ou "Sandbox".
4. Permitir Conexão depois de conectar na sua conta Moip.
5. Preencher Nome da Fatura e Prefixo no extrato Moip.
6. Marcar o checkbox "Habilitar Pagamento".
7. Escolher o tipo de pagamento: "Checkou Padrão", "Checkout Transaparente", "Checkout Moip".
8. Salvara alterações.

== Screenshots ==

1. Home screen
2. Credit Card Settings
3. Billet Banking Settings
4. Payment Split Settings
5. Tools Settings

== Frequently Asked Questions ==

= Não consigo configurar o split de pagamento! =

Criamos um passo a passo para auxiliar na configuração do [Split de Pagamento](https://blog.apiki.com/e-commerce/)

= Onde posso obter suporte ao plugin? =

Você pode utilizar o forúm do plugin [Moip Brasil Oficial](https://wordpress.org/support/plugin/woo-moip-official/)

Você pode utilizar o canal de atendimento, [Suporte](https://apiki.com/parceiros/moip/).

= Erro ao autorizar o plugin =

Antes de autorizar, confere se os links permanentes estão como Nome do Post.

Não pode possuir nenhum parâmetro na url, exemplo: https://urldosite.com/index.php.

Se o site estiver em modo de manutenção, não funcionará a autorização.

= Não está enviando e-mail =

O plugin não envia e-mail, talvez o servidor não está configurado o envio de e-mail,
recomendamos utilizar algum plugin de SMTP para resolver esse problema.

= Funciona com todos plugins de marketplace? =

Integração com o plugin de marketplace [Dokan](https://wordpress.org/plugins/dokan-lite/) e [WCFM](https://wordpress.org/plugins/wc-multivendor-marketplace/).
Mas já estamos fazendo integração com outros.

= Mesma conta Wirecard em vários sites? =

Por enquanto não funciona utilizar a mesma conta Wirecard em sites diferentes, precisa criar contas diferentes para cada site.

= Status do pedido parou de funcionar =

Autorize novamente o plugin ou utilize o botão Atualizar Notificações na aba Ferramentas.


== Changelog ==

= 1.4.7.4 - 23/11/2022 =
- Removendo email do admin e adicionando customer id.
- Resolvendo problema de CUSTOMER ERROR.

= 1.4.7.3 - 20/09/2022 =
- Adicionando opção no aba de boleto para aplicar o desconto no total ou no subtotal.

= 1.4.7.2 - 15/07/2022 =
- Resolvendo da taxa de cartão duplicada.
- Resolvendo problema de não exibir a quantidade de parcelas corretamente.
- Resolvendo problema de autorização com sandbox.
- Removendo a opção do checkout padrão.
- Preparando o plugin para mudança de estrutura na versão 2.0.0.

= 1.4.7.1 - 04/03/2022 =
- Resolvendo o problema da public-key no checkout - ( Créditos: Damian Gonzalez ).

= 1.4.7 - 04/03/2022 =
- Resolvendo problema de url do moip na dashboard do Dokan.
- Resolvendo problema de hash do cartão.
- Resolvendo problema da data de compra do cartão.

= 1.4.6 - 20/12/2021 =
- Resolvendo problema de redirect para conta do lojista.
- Adicionando juros no cartão para compra à vista.
- Resolvendo problema de mensagem de cancelamento.

= 1.4.5 - 30/10/2021 =
- Wirecard virou Moip, alterando a identidade visual de Wirecard para Moip.

= 1.4.4 - 24/10/2021 =
- Removendo do select do cartão para escolher a parcela, deixando à vista como padrão.
- Adicionando informação de juros de parcelamento nos detalhes do pedido.
- Alterando data de expiração do cartão MM/YY.
- Preparando plugin para mudança de checkout.

= 1.4.3.5 - 07/08/2021 =
- Resolvendo problema geração de APP para split.

= 1.4.3.4 - 02/08/2021 =
- Resolvendo problema de HTML no checkout.

= 1.4.3.3 - 27/07/2021 =
- Atualização para ativação no WordPress.org.

= 1.4.3 - 13/04/2021 =
- Correção da taxa de juros do cartão de crédito.

= 1.4.2 - 19/03/2021 =
- Correção das máscaras dos campos opcionais do cartão de crédito.
- Correção do link de impressão do boleto.
- Adicionando campo para envio de taxas.

= 1.4.1 - 24/02/2021 =
- Correção durante atualização.

= 1.4.0 - 24/02/2021 =
- Integração com o marketplace WCFM.
- Correção de status cancelado.
- Correção de envio do endereço do pedido.

= 1.3.7.5 - 24/11/2020 =
- Adicionando mensagem de validação para o ano de expiração do cartão.

= 1.3.7.4 - 07/10/2020 =
- Alterando data de expiração do cartão MM/YYYY.
- Adicionando validações na página de checkout.
- Adicionando validações na área de split de pagamento.
- Adicionando log de erro nas notas do pedido.

= 1.3.7.3 - 01/09/2020 =
- Correção de notificações de status de pagamentos autorizados.

= 1.3.7.2 - 28/08/2020 =
- Correção de notificações de status.
- Adicionando opção para pedido cancelado ou malsucedido.

= 1.3.7.1 - 20/08/2020 =
- Correção StatementDescriptor no checkout.
- Adicionando permission_callback na API do split de pagamento.

= 1.3.7 - 18/08/2020 =
- Alterando data de expiração do cartão MM/YY.
- Adicionando campo para alteração do nome do beneficiário no boleto.
- Correções em geral.

= 1.3.6 - 04/06/2020 =
- Adicionando botão para Atualizar Notificações em ferramentas.
- Adicionando campos personalizados no pedido.

= 1.3.5 - 02/06/2020 =
- Removendo informaçãoes desnecessárias do admin.
- Obrigatório ativar plugin de marketplace Dokan para uso do split.
- Adicionando opção para escolher quem paga a taxa do frete no split.
- Atualizando a SDK.

= 1.3.4 - 28/02/2020 =
- Pedidos atualizados de acordo com juros no cartão de crédito.
- Adicionando opção em ferramentas para alterar o status do pedido.
- Resolvendo problema do produto com o valor zerado.
- Resolvendo problema do status do pedido.

= 1.3.3 - 20/01/2020 =
- Resolvendo problema de JS no botão de autorizar.
- Adicionando campos para gerar o APP do Split de pagamento manualmente.

= 1.3.2 - 07/01/2020 =
- Alterando layout do email do boleto.
- Adicionando campos para email do boleto na administração do plugin.
- Adicionando nome da empresa no boleto quando pessoa jurídica ( Contribuição: Mauro Capuano ).

= 1.3.1 - 25/10/2019 =
- Adicionando taxa do split para o recebedor primário.
- Resolvendo problema de integração com o plugin Product Bundles.
- Alterando forma de criação do APP para o split de pagamento.
- Adicionando campo de telefone no cartão de crédito para análise de risco.
- Adicionando campo de data de nascimento no cartão de crédito para análise de risco.

= 1.3.0 - 08/10/2019 =
- Criação do split de pagamento.
- Integração com o marketplace Dokan.
- Resolvendo problema com a SDK.

= 1.2.9.1 - 16/09/2019 =
- Resolvendo problema com o campo de CPF no checkout transparente.
- Atualizando SDK.
- Split de pagamento.

= 1.2.9 - 12/08/2019 =
- Adicionando campo de CPF no cartão de crédito para análise de risco.
- Adicionando checagem de parâmetro na url antes de autorizar o plugin.
- Resolvendo problema de envio de email do boleto.

= 1.2.8.2 - 28/06/2019 =
- Resolvendo problema status do pedido por boleto.

= 1.2.8.1 - 24/06/2019 =
- Atualizando url do suporte no plugin.
- Resolvendo problema do frete gratuito.
- Adicionando bandeira do cartão no admin do pedido.

= 1.2.8 - 10/05/2019 =
- Adicionando submenu na administração do plugin.
- Adicionando descrição para compra com desconto.
- Resolvendo conflito com plugin de desconto.
- Adicionando informações nos campos de boleto e cartão na página de checkout.

= 1.2.7.2 - 09/04/2019 =
- Resolvendo conflito com o plugin WooCommerce Extra Checkout Fields for Brazil.

= 1.2.7.1 - 05/04/2019 =
- Resolvendo problema na quantidade de parcelas.
- Resolvendo conflito com os campos do checkout transparente.
- Resolvendo conflito do checkout padrão.

= 1.2.7 - 04/04/2019 =
- Resolvendo problema de criação de pedido quando ocorre erro no checkout.
- Ajustando textos dos campos obrigatórios.
- Adicionando envio do boleto para o e-mail.
- Alterando o desconto Wirecard para desconto somente no boleto.
- Adicionando campo para digitar o nome do portador do cartão de crédito.
- Adicionando campo em configurações para escolher o tipo de pessoa.

= 1.2.6 - 07/02/2019 =
- Resolvendo problema de autorização.
- Alterando o desconto no boleto para desconto Wirecard.

= 1.2.5 - 20/12/2018 =
- Ajuste visual nas parcelas do checkout transparente.
- Alterando texto na página de finalização de compra.
- Resolvendo problema de notificações.

= 1.2.4 - 26/11/2018 =
- Resolvendo problema de preço nos pedidos com desconto.
- Adicionando opção de texto na aba de boleto no checkout transparente.
- Adicionando opção nas configurações para salvar o cartão de crédito.

= 1.2.3 - 21/11/2018 =
- Resolvendo problema de duplicidade no status do webhook nos pedidos.
- Resolvendo problema de alterações do preço do produto por outros plugins.
- Resolvendo problema do tipo de compra não estava aparecendo nos pedidos.
- Ajustes visual no checkout transparente.

= 1.2.2 - 05/11/2018 =
- Moip virou Wirecard, alterando a identidade visual de Moip para Wirecard.

= 1.2.1 - 09/10/2018 =
- Opção no admin do plugin para utilizar a mesma conta do Moip em vários sites.
- Botão no admin do plugin para reenviar webhooks caso esteja com o status do woocommerce/moip.
- Resolvendo o problema onde o desconto do Moip aparecia para outros meios de pagamento.
- Mostrando em pedidos do woocommerce no admin se a compra foi feita por cartão ou boleto.
- Deletando webhooks quando estiver autorizando o plugin.

= 1.2.0 - 13/08/2018 =
- Implementação de checkout transparente na página de seleção do tipo de pagamento (checkout).
- Implementando opção de definir o valor mínimo no carrinho para habilitar parcelamento.
- Implementando suporte a desconto no boleto (apenas no checkout transparente).

= 1.1.8 - 20/02/2018 =
- Ajuste na manipulação das tabs no checkout
- Implementando armazenamento da quantidade de parcelas selecionadas no checkout

= 1.1.7 - 09/02/2018 =
- Removendo jshint para atender as especificações do wordpress.org

= 1.1.6 - 11/01/2018 =
- Implementação de cancelamento do pedido após retorno do Moip;
- Ocultando opção de pagamento via débito online no checkout transparente;
- Exibindo informações sobre os webhooks (notificações) na administração.

= 1.1.5 - 12/12/2017 =
- Corrigindo bug quando selecionado apenas pessoa física no plugin WooCommerce Extra Checkout Fields For Brazil.
- Adicionando suporte a logs.

= 1.1.4 - 30/10/2017 =
- Adicionando suporte a guest checkout.

= 1.1.3 - 27/10/2017 =
- Corrigindo bug de javascript no checkout quando utilizado apenas boleto.

= 1.1.2 - 04/10/2017 =
- Renomeando arquivo principal do plugin
- Melhorias na internacionalização do arquivos

= 1.1.1 - 22/09/2017 =
- Correções de erros

= 1.1.0 - 20/09/2017 =
- Correções de erros e melhorias de performance

= 1.0.1 - 13/09/2017 =
- Implementando configuração de parcelamento por bandeira

= 1.0.0 - 30/08/2017 =
- Release inicial
