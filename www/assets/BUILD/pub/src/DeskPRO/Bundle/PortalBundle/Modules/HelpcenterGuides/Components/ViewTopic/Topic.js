import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage } from 'react-intl';
import moment from 'moment';
import { copyTextToClipboard } from 'DeskPRO/Component/Util/ClipBoard';
import { TopicSummary } from '../index';

class Topic extends React.PureComponent {
  static propTypes = {
    intl:      PropTypes.object,
    topic:     PropTypes.object,
    data:      PropTypes.object,
    guideSlug: PropTypes.string,
    topicSlug: PropTypes.string,
    sizes:     PropTypes.object,
    loaded:    PropTypes.bool,
  };

  static defaultProps = {
    data: {}
  };

  constructor(props) {
    super(props);
    this.state = {
      copied: false
    };
  }

  copyLinkToClipBoard = (e) => {
    e.preventDefault();
    const { topic, guideSlug } = this.props;
    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    const url = `${window.location.origin}${baseUrl}/guides/${guideSlug}/${topic.slug}`;
    if (copyTextToClipboard(url)) {
      this.setState({
        copied: true
      });
      setTimeout(() => { this.setState({ copied: false }); }, 1000);
    }
    return false;
  };

  render() {
    const { data, topic, guideSlug, topicSlug, intl, sizes, loaded } = this.props;

    const fixed = false;
    const agentBarHeight = 0;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    const style = {};
    if (sizes && topic.slug === topicSlug) {
      style.width = sizes.articleWidth;
      style.position = 'fixed';
    }
    const topicStyle = {};
    if (!loaded) {
      topicStyle.display = 'none';
    }

    return (
      <div className="dp-po-guides-block-article" id={`topic_${topic.slug}`} style={topicStyle}>
        <div className="row">
          <div className="col-sm-9">
            <div className="dp-po-guides-block-article-left">
              <div className="dp-po-guides-block-header">
                <div>
                  <h2 className="dp-po-guides-block-title dp-po-clipboard">
                    {topic.title}
                    <a
                      className="dp-po-clipboard-link"
                      data-toggle="tooltip"
                      data-placement="top"
                      href={`${baseUrl}/guides/${guideSlug}/${topic.slug}`}
                      onClick={this.copyLinkToClipBoard}
                    >
                      <i className="dp-po-icon far fa-anchor" title={intl.formatMessage({ id: 'helpcenter.general.copy_to_clipboard' })} />
                      {this.state.copied && <FormattedMessage id="helpcenter.general.copied" />}
                    </a>
                  </h2>
                  { topic.parent &&
                  <a href="" className="dp-po-guides-block-chapter"><i
                    className="dp-po-icon fal fa-angle-right"
                  />
                    {topic.parent.title}</a>
                  }
                </div>
                <div className="dp-po-guides-block-extra">
                  <ul className="dp-po-guides-block-extra-list">
                    {/* <li className="dp-po-guides-block-extra-item">*/}
                    {/*  <a href="" className="dp-po-guides-block-extra-link"><i*/}
                    {/*    className="dp-po-icon fal fa-print"*/}
                    {/*  /></a>*/}
                    {/* </li>*/}
                    {/* <li className="dp-po-guides-block-extra-item">*/}
                    {/*  <a href="" className="dp-po-guides-block-extra-link">*/}
                    {/*    <i className="dp-po-icon fal fa-file-pdf" />*/}
                    {/*  </a>*/}
                    {/* </li>*/}
                  </ul>
                </div>
              </div>
              <div
                className="dp-po-post-content dp-po-guides-block-content"
                dangerouslySetInnerHTML={{ __html: data.content }}
              />
            </div>
          </div>
          <div className="col-sm-3">
            <div className="dp-po-guides-block-article-right" style={style}>
              <TopicSummary content={data.content} fixed={fixed} agentBarHeight={agentBarHeight} />
              <div className="dp-po-guides-meta">
                <p>Published: <strong>{moment(data.date_published).format('DD/MM/YYYY')}</strong></p>
                <p>Last updated: <strong>{moment(data.date_updated).format('DD/MM/YYYY')}</strong></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default injectIntl(Topic);
