// To parse this JSON data, do
//
//     final withdrawFlutterWaveInsertModel = withdrawFlutterWaveInsertModelFromJson(jsonString);

import 'dart:convert';

WithdrawFlutterWaveInsertModel withdrawFlutterWaveInsertModelFromJson(
        String str) =>
    WithdrawFlutterWaveInsertModel.fromJson(json.decode(str));

String withdrawFlutterWaveInsertModelToJson(
        WithdrawFlutterWaveInsertModel data) =>
    json.encode(data.toJson());

class WithdrawFlutterWaveInsertModel {
  Message message;
  Data data;

  WithdrawFlutterWaveInsertModel({
    required this.message,
    required this.data,
  });

  factory WithdrawFlutterWaveInsertModel.fromJson(Map<String, dynamic> json) =>
      WithdrawFlutterWaveInsertModel(
        message: Message.fromJson(json["message"]),
        data: Data.fromJson(json["data"]),
      );

  Map<String, dynamic> toJson() => {
        "message": message.toJson(),
        "data": data.toJson(),
      };
}

class Data {
  PaymentInformations paymentInformations;
  String gatewayType;
  String gatewayCurrencyName;
  String alias;
  String url;
  String method;
  bool branchAvailable;

  Data({
    required this.paymentInformations,
    required this.gatewayType,
    required this.gatewayCurrencyName,
    required this.alias,
    required this.url,
    required this.method,
    required this.branchAvailable,
  });

  factory Data.fromJson(Map<String, dynamic> json) => Data(
        paymentInformations:
            PaymentInformations.fromJson(json["payment_informations"]),
        gatewayType: json["gateway_type"],
        gatewayCurrencyName: json["gateway_currency_name"],
        alias: json["alias"],
        url: json["url"],
        method: json["method"],
        branchAvailable: json["branch_available"],
      );

  Map<String, dynamic> toJson() => {
        "payment_informations": paymentInformations.toJson(),
        "gateway_type": gatewayType,
        "gateway_currency_name": gatewayCurrencyName,
        "alias": alias,
        "url": url,
        "method": method,
        "branch_available": branchAvailable,
      };
}

class PaymentInformations {
  String trx;
  String gatewayCurrencyName;
  String requestAmount;
  String exchangeRate;
  String conversionAmount;
  String totalCharge;
  String willGet;
  String payable;

  PaymentInformations({
    required this.trx,
    required this.gatewayCurrencyName,
    required this.requestAmount,
    required this.exchangeRate,
    required this.conversionAmount,
    required this.totalCharge,
    required this.willGet,
    required this.payable,
  });

  factory PaymentInformations.fromJson(Map<String, dynamic> json) =>
      PaymentInformations(
        trx: json["trx"],
        gatewayCurrencyName: json["gateway_currency_name"],
        requestAmount: json["request_amount"],
        exchangeRate: json["exchange_rate"],
        conversionAmount: json["conversion_amount"],
        totalCharge: json["total_charge"],
        willGet: json["will_get"],
        payable: json["payable"],
      );

  Map<String, dynamic> toJson() => {
        "trx": trx,
        "gateway_currency_name": gatewayCurrencyName,
        "request_amount": requestAmount,
        "exchange_rate": exchangeRate,
        "conversion_amount": conversionAmount,
        "total_charge": totalCharge,
        "will_get": willGet,
        "payable": payable,
      };
}

class Message {
  List<String> success;

  Message({
    required this.success,
  });

  factory Message.fromJson(Map<String, dynamic> json) => Message(
        success: List<String>.from(json["success"].map((x) => x)),
      );

  Map<String, dynamic> toJson() => {
        "success": List<dynamic>.from(success.map((x) => x)),
      };
}
