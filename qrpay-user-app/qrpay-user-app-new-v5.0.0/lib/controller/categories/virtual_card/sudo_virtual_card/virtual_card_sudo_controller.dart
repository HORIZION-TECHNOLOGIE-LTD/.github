import 'package:get/get.dart';
import 'package:intl/intl.dart';
import 'package:qrpay/backend/model/common/common_success_model.dart';

import '../../../../backend/local_storage/local_storage.dart';
import '../../../../backend/model/categories/virtual_card/virtual_card_flutter_wave/card_transaction_model.dart';
import '../../../../backend/model/categories/virtual_card/virtual_card_sudo/VirtualCardSudoInfoModel.dart';
import '../../../../backend/model/categories/virtual_card/virtual_card_sudo/sudo_card_details_model.dart';
import '../../../../backend/services/api_services.dart';
import '../../../../backend/utils/logger.dart';
import '../../../../routes/routes.dart';
import '../../../navbar/dashboard_controller.dart';

final log = logger(VirtualSudoCardController);

class VirtualSudoCardController extends GetxController {
  RxString baseCurrency = "".obs;
  List<String> baseCurrencyList = [];
  final dashboardController = Get.find<DashBoardController>();
  RxDouble fee = 0.0.obs;
  RxDouble limitMin = 0.0.obs;
  RxDouble limitMax = 0.0.obs;
  RxDouble percentCharge = 0.0.obs;
  RxDouble fixedCharge = 0.0.obs;
  RxDouble rate = 0.0.obs;
  RxDouble totalFee = 0.0.obs;
  RxInt activeIndex = 0.obs;
  RxInt activeIndicatorIndex = 0.obs;
  RxString cardImage = ''.obs;
  RxString cardBackDetails = ''.obs;
  RxString siteTitle = ''.obs;
  RxString siteLogo = ''.obs;

  @override
  void onInit() {
    if (LocalStorages.getCardType() == 'sudo') {
      getCardInfoData();
      ();
    }
    super.onInit();
  }

  changeIndicator(int value) {
    activeIndicatorIndex.value = value;
  }

  String getDay(String value) {
    DateTime startDate = DateTime.parse(value);
    var date = DateFormat('dd').format(startDate);
    return date.toString();
  }

  String getMonth(String value) {
    DateTime startDate = DateTime.parse(value);
    var date = DateFormat('MMMM').format(startDate);
    return date.toString();
  }

  // ---------------------------------------------------------------------------
  //                              Get Card Info Data
  // ---------------------------------------------------------------------------

  // -------------------------------Api Loading Indicator-----------------------
  //
  final _isLoading = false.obs;

  bool get isLoading => _isLoading.value;

  // -------------------------------Define API Model-----------------------------
  //
  late VirtualCardSudoInfoModel _cardInfoModel;

  VirtualCardSudoInfoModel get cardInfoModel => _cardInfoModel;

  // ------------------------------API Function---------------------------------
  //
  Future<VirtualCardSudoInfoModel> getCardInfoData() async {
    _isLoading.value = true;
    update();

    await ApiServices.sudoCardInfoApi().then((value) {
      _cardInfoModel = value!;
      baseCurrency.value = _cardInfoModel.data.baseCurr;
      baseCurrencyList.add(_cardInfoModel.data.baseCurr);

      limitMin.value = _cardInfoModel.data.cardCharge.minLimit;
      limitMax.value = _cardInfoModel.data.cardCharge.maxLimit;
      percentCharge.value = _cardInfoModel.data.cardCharge.percentCharge;
      fixedCharge.value = _cardInfoModel.data.cardCharge.fixedCharge;
      rate.value = 1.0;

      cardImage.value = _cardInfoModel.data.cardBasicInfo.cardBg;
      cardBackDetails.value = _cardInfoModel.data.cardBasicInfo.cardBackDetails;
      siteTitle.value = _cardInfoModel.data.cardBasicInfo.siteTitle;
      siteLogo.value = _cardInfoModel.data.cardBasicInfo.siteLogo;

      update();
    }).catchError((onError) {
      log.e(onError);
    });

    _isLoading.value = false;
    update();
    return _cardInfoModel;
  }

  // ---------------------------------------------------------------------------
  //                              Get Card Details Data
  // ---------------------------------------------------------------------------

  // -------------------------------Api Loading Indicator-----------------------
  //
  final _isDetailsLoading = false.obs;

  bool get isDetailsLoading => _isDetailsLoading.value;

  // -------------------------------Define API Model-----------------------------
  //
  late SudoCardDetailsModel _cardDetailsModel;

  SudoCardDetailsModel get cardDetailsModel => _cardDetailsModel;

  // ------------------------------API Function---------------------------------
  //
  Future<SudoCardDetailsModel> getCardDetailsData(String id) async {
    _isDetailsLoading.value = true;
    update();

    await ApiServices.sudoCardDetailsApi(id: id).then((value) {
      _cardDetailsModel = value!;

      update();
    }).catchError((onError) {
      log.e(onError);
    });

    _isDetailsLoading.value = false;
    update();
    return _cardDetailsModel;
  }

  // ---------------------------------------------------------------------------
  //                              Card Block Process
  // ---------------------------------------------------------------------------

  // -------------------------------Define API Model-----------------------------
  //
  late CommonSuccessModel _cardBlockModel;

  CommonSuccessModel get cardBlockModel => _cardBlockModel;

  // ------------------------------API Function---------------------------------
  //
  Future<CommonSuccessModel> cardBlockProcess(String cardId) async {
    _isLoading.value = true;
    Map<String, dynamic> inputBody = {'card_id': cardId};

    update();

    await ApiServices.sudoCardBlockApi(body: inputBody).then((value) {
      _cardBlockModel = value!;
      getCardDetailsData(cardId);
      update();
    }).catchError((onError) {
      log.e(onError);
    });

    _isLoading.value = false;
    update();
    return _cardBlockModel;
  }

  // ---------------------------------------------------------------------------
  //                              Card Unblock Process
  // ---------------------------------------------------------------------------

  // -------------------------------Define API Model-----------------------------
  //

  late CommonSuccessModel _cardUnBlockModel;

  CommonSuccessModel get cardUnBlockModel => _cardUnBlockModel;

  // ------------------------------API Function---------------------------------
  //
  Future<CommonSuccessModel> cardUnBlockProcess(String cardId) async {
    _isLoading.value = true;
    Map<String, dynamic> inputBody = {'card_id': cardId};

    update();

    await ApiServices.sudoCardUnBlockApi(body: inputBody).then((value) {
      _cardUnBlockModel = value!;
      getCardDetailsData(cardId);
      update();
    }).catchError((onError) {
      log.e(onError);
    });

    _isLoading.value = false;
    update();
    return _cardUnBlockModel;
  }

  final _isMakeDefaultLoading = false.obs;

  bool get isMakeDefaultLoading => _isMakeDefaultLoading.value;

  late CommonSuccessModel _cardDefaultModel;

  CommonSuccessModel get cardDefaultModel => _cardDefaultModel;

  // ------------------------------API Function---------------------------------
  //
  Future<CommonSuccessModel> makeCardDefaultProcess(String cardId) async {
    _isMakeDefaultLoading.value = true;
    Map<String, dynamic> inputBody = {'card_id': cardId};

    update();

    await ApiServices.sudoCardMakeOrRemoveDefaultApi(body: inputBody)
        .then((value) {
      _cardDefaultModel = value!;
      getCardInfoData();
      update();
    }).catchError((onError) {
      log.e(onError);
    });

    _isMakeDefaultLoading.value = false;
    update();
    return _cardDefaultModel;
  }

  // ---------------------------------------------------------------------------
  //                              Get Card Transaction Data
  // ---------------------------------------------------------------------------

  // -------------------------------Define API Model-----------------------------
  //
  late CardTransactionModel _cardTransactionModel;

  CardTransactionModel get cardTransactionModel => _cardTransactionModel;

  // ------------------------------API Function---------------------------------
  //
  Future<CardTransactionModel> getCardTransactionData(String id) async {
    _isLoading.value = true;
    update();
    Get.toNamed(Routes.transactionHistoryScreen);
    await ApiServices.cardTransactionApi(id: id).then((value) {
      _cardTransactionModel = value!;
      update();
    }).catchError((onError) {
      log.e(onError);
    });

    _isLoading.value = false;
    update();
    return _cardTransactionModel;
  }

  void buyCard() {}

  // -------------------------------Api Loading Indicator-----------------------

  late CommonSuccessModel _cardCreateData;

  CommonSuccessModel get cardCreateData => _cardCreateData;

  Future<CommonSuccessModel> cardCreateProcess(String cardAmount) async {
    _isLoading.value = true;
    Map<String, dynamic> inputBody = {
      'card_amount': cardAmount,
    };

    update();

    await ApiServices.createCardApi(body: inputBody).then((value) {
      _cardCreateData = value!;

      update();
    }).catchError((onError) {
      log.e(onError);
    });

    _isLoading.value = false;
    update();
    return _cardCreateData;
  }
}
