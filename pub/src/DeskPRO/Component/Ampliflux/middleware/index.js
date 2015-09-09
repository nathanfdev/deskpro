import actionThunkMiddleware from "./actionThunkMiddleware";
import redispatchDsaPayload from "./redispatchDsaPayload";
import guidMiddleware from "./guidMiddleware";
import intervalMiddleware from "./intervalMiddleware";
import loggerMiddleware from "./loggerMiddleware";
import promiseMiddleware from "./promiseMiddleware";
import timeoutMiddleware from "./timeoutMiddleware";
import timerMiddleware from "./timerMiddleware";

export default {
  actionThunkMiddleware,
  timerMiddleware,
  redispatchDsaPayload,
  guidMiddleware,
  intervalMiddleware,
  loggerMiddleware,
  promiseMiddleware,
  timeoutMiddleware,
  timerMiddleware
}
