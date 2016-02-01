import { DpxFormAttachDraft } from './DpxFormAttachDraft';

export class DpxFormRteBlobsDraft extends DpxFormAttachDraft {

  getName() {
    return super.getName() + '[blobs]';
  }
}
