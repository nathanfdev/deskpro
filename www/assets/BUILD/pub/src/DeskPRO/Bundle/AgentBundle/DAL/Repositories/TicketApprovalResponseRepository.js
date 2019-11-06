import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class TicketApprovalResponseRepository extends ApiRepository {
  getAll(approvalRequestId) {
    return this.api.sendGet(`DP_API/${this.url}/${approvalRequestId}`);
  }
}
export default TicketApprovalResponseRepository;
