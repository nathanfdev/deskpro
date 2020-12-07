import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import debounce from 'lodash/debounce';

export class HelpcenterFileDownload extends PageWidget {
  renderWidget() {
    const debouncedDownloadFile = debounce(this.downloadFile, 3600, {
      leading: true,
    });
    window.document.getElementById('file-download-link').addEventListener('click', (e) => {
      const url = e.target.dataset.url;
      debouncedDownloadFile(url);
    });
  }

  downloadFile = (url) => {
    window.location = url;
  }
}
