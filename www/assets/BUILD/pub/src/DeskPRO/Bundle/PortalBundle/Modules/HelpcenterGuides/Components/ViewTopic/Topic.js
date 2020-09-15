import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage, FormattedDate } from 'react-intl';
import $ from 'jquery';
import { copyTextToClipboard } from 'DeskPRO/Component/Util/ClipBoard';
import { TopicSummary } from '../index';
import AuthorsAvatars from './AuthorsAvatars';

class Topic extends React.PureComponent {
  static propTypes = {
    intl:      PropTypes.object,
    topic:     PropTypes.object,
    topicList: PropTypes.array,
    guideSlug: PropTypes.string,
    topicSlug: PropTypes.string,
    sizes:     PropTypes.object,
    loaded:    PropTypes.bool,
  };

  static defaultProps = {
    topic:     {},
    topicList: []
  };

  constructor(props) {
    super(props);
    this.anchor = React.createRef();
  }

  copyLinkToClipBoard = (e) => {
    e.preventDefault();
    const { topic, guideSlug, intl } = this.props;
    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    const url = `${window.location.origin}${baseUrl}/guides/${guideSlug}/${topic.slug}`;
    if (copyTextToClipboard(url)) {
      $(this.anchor.current).attr('data-original-title', intl.formatMessage({ id: 'helpcenter.general.copied' })).tooltip('show');
      setTimeout(() => {
        $(this.anchor.current).attr('data-original-title', intl.formatMessage({ id: 'helpcenter.general.copy_to_clipboard' })).tooltip('hide');
      }, 1000);
    }
    return false;
  };

  render() {
    const { topic, guideSlug, topicSlug, intl, sizes, loaded, topicList } = this.props;

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

    const parent = topicList.find(t => t.id === topic.parent);

    return (
      <div className="dp-po-guides-block-article" id={`topic_${topic.slug}`} style={topicStyle}>
        <div className="row">
          <div className="col-sm-9">
            <div className="dp-po-guides-block-article-left">
              <div className="dp-po-guides-block-header">
                <h2 className="dp-po-guides-block-title dp-po-clipboard">
                  {topic.title}
                  {topic.status === 'hidden' ?
                    <span
                      className="dp-po-icon dp-info" data-toggle="tooltip"
                      title={intl.formatMessage({ id: 'helpcenter.general.viewed_by_agents_only' })} data-placement="top"
                    >
                      <i className="fal fa-info-circle text-primary" />
                    </span>
                    : null}
                  <a
                    className="dp-po-clipboard-link"
                    data-toggle="tooltip"
                    data-placement="top"
                    href={`${baseUrl}/guides/${guideSlug}/${topic.slug}`}
                    onClick={this.copyLinkToClipBoard}
                    ref={this.anchor}
                    title={intl.formatMessage({ id: 'helpcenter.general.copy_to_clipboard' })}
                  >
                    <span className="dp-po-icon far fa-anchor" />
                  </a>
                </h2>
                <div className="dp-po-guides-block-title-section">
                  <FormattedMessage id="helpcenter.guides.in_section" values={{ section: parent.title }} />
                </div>
                <AuthorsAvatars authors={topic.authors} />
                <div className="dp-po-guides-meta">
                  {topic.date_published && <Fragment><FormattedMessage id="helpcenter.general.published" />: <strong><FormattedDate value={topic.date_published} day="numeric" month="short" year="numeric" /></strong><span className="separator">|</span></Fragment>}
                  <FormattedMessage id="helpcenter.general.last_updated" />: <strong><FormattedDate value={topic.date_updated} day="numeric" month="short" year="numeric" /></strong>
                </div>
                <div className="dp-po-guides-block-extra">
                  <ul className="dp-po-guides-block-extra-list">
                    <li className="dp-po-guides-block-extra-item">
                      <a href="" className="dp-po-guides-block-extra-link"><i
                        className="dp-po-icon fal fa-print"
                      /></a>
                    </li>
                    <li className="dp-po-guides-block-extra-item">
                      <a href="" className="dp-po-guides-block-extra-link">
                        <i className="dp-po-icon fal fa-file-pdf" />
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <div
                className="dp-po-post-content dp-po-guides-block-content"
                dangerouslySetInnerHTML={{ __html: topic.content }}
              />
            </div>
          </div>
          <div className="col-sm-3">
            <div className="dp-po-guides-block-article-right" style={style}>
              <TopicSummary content={topic.content} fixed={fixed} agentBarHeight={agentBarHeight} />
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default injectIntl(Topic);
