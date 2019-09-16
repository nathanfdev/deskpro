import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class TicketApprovalRepository extends ApiRepository {
  cancelApprovalRequest(approvalRequestId) {
    return this.api.sendPut(`DP_API/${this.url}/${approvalRequestId}/cancel`);
  }

  acceptApprovalRequest(approvalRequestId, data = {}) {
    return this.api.sendPost(`DP_API/${this.url}/${approvalRequestId}/approve`, data);
  }

  rejectApprovalRequest(approvalRequestId, data = {}) {
    return this.api.sendPost(`DP_API/${this.url}/${approvalRequestId}/reject`, data);
  }
}
export default TicketApprovalRepository;
