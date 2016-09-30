var SolrResult = Class.create();
SolrResult.prototype = {

    isFirstCall: true,
    
    baseUrl: null,
    
    initialize: function(baseUrl) {
        this.baseUrl = baseUrl;
        this.updateLinks();
    },

    updateResults: function (url) {
        var self = this;
        var contentElement = $$('.col-main')[0];
        var ajaxUrl = this.baseUrl + '?' + this.getUrlParametersAsArray(url).join('&');
        new Ajax.Request(ajaxUrl, {
            onCreate: function () {
                $('solr-loader').addClassName('active');
            },
            onSuccess: function (response) {
                var responseBody = JSON.parse(response.responseText);
                var element;
                if (element = $$('.col-main .page-title')[0]) {
                    element.remove();
                }
                if (element = $$('.col-main .category-image')[0]) {
                    element.remove();
                }
                if (element = $$('.col-main .category-products')[0]) {
                    element.replace(responseBody['products']);
                }
                if (element = $$('.col-main .block-layered-nav')[0]) {
                    element.replace(responseBody['topnav']);
                }
                if (element = $$('.col-main .block-filter-nav')[0]) {
                    element.replace(responseBody['topnav']);
                }
                if (element = $$('.col-left .block-layered-nav')[0]) {
                    element.replace(responseBody['leftnav']);
                }
            },
            onComplete: function (response) {
                self.updateLinks(ajaxUrl);
                self.updateUrl(ajaxUrl);
                $('solr-loader').removeClassName('active');
            }
        });
    },
    
    updateUrl: function(ajaxUrl) {
        var currentUrl = window.location.href;
        var urlBasePart = this.getUrlBasePart(currentUrl);
        var newUrl = urlBasePart + '?' + this.getUrlParametersAsArray(ajaxUrl).join('&');
        window.history.pushState(null, '', newUrl);
    },
    
    updateLinks: function (ajaxUrl) {
        //this.updateLinkURLs(ajaxUrl);
        this.updateLinkObservers();
    },

    updateLinkURLs: function (url) {
        if (!url) {
            return;
        }
        var newParameters = this.getUrlParametersAsArray(url);
        var originalParameters = this.getUrlParametersAsArray(window.location.href);
        if (newParameters.length > originalParameters.length) {
            for (var index = 0; index < newParameters.length; index++) {
                var parameter = newParameters[index];
                if (originalParameters.indexOf(parameter) == -1) {
                    this.addParamToFilterUrls(parameter);
                }
            }
        } else if (newParameters.length < originalParameters.length) {
            for (var index = 0; index < originalParameters.length; index++) {
                var parameter = originalParameters[index];
                if (newParameters.indexOf(parameter) == -1) {
                    this.removeParamFromFilterUrls(parameter);
                }
            }
        } else {
            for (var index = 0; index < newParameters.length; index++) {
                var newParameter = newParameters[index];
                var newParameterParts = newParameter.split('=');
                var newParameterKey = newParameterParts[0];
                var newParameterValues = newParameterParts[1].split(encodeURIComponent(','));
                for (var originalIndex = 0; originalIndex < originalParameters.length; originalIndex++) {
                    var originalParameter = originalParameters[originalIndex];
                    var originalParameterParts = originalParameter.split('=');
                    var originalParameterKey = originalParameterParts[0];
                    var originalParameterValues = originalParameterParts[1].split(encodeURIComponent(','));

                    if ((originalParameterKey == newParameterKey) && (originalParameter != newParameter)) {
                        if (originalParameterValues.length < newParameterValues.length) {
                            for (var newParameterIndex = 0; newParameterIndex < newParameterValues.length; newParameterIndex++) {
                                var newParameterValue = newParameterValues[newParameterIndex];
                                if (originalParameterValues.indexOf(newParameterValue) == -1) {
                                    this.addParamToFilterUrls(newParameterKey + '=' + newParameterValue);
                                }
                            }
                        } else {
                            for (var originalParameterIndex = 0; originalParameterIndex < originalParameterValues.length; originalParameterIndex++) {
                                var originalParameterValue = originalParameterValues[originalParameterIndex];
                                if (newParameterValues.indexOf(originalParameterValue) == -1) {
                                    this.removeParamFromFilterUrls(originalParameterKey + '=' + originalParameterValue);
                                }
                            }
                        }
                    }
                }
            }
        }
    },

    updateLinkObservers: function () {
        var self = this;
        var links;
        if (this.isFirstCall) {
            links = $$('.block-layered-nav a', '.block-filter-nav a', '.toolbar a', '.toolbar-bottom a');
            this.isFirstCall = false;
        } else {
            links = $$('.block-layered-nav a', '.block-filter-nav a', '.toolbar a', '.toolbar-bottom a');
        }

        links.each(function (element) {
            element.observe('click', function (e) {
                Event.stop(e);
                var url = element.href;

                self.updateResults(url);
                element.select('input[type=checkbox]').each(function(checkbox) {
                    checkbox.checked = !checkbox.checked;
                });

                element.select('.crossbox').each(function(checkbox) {
                    checkbox.toggleClassName('crossbox--checked');
                });
            });
        });

        var dropdowns = $$('.toolbar select', '.toolbar-bottom select');

        dropdowns.each(function (element) {
            element.onchange = undefined;
            element.observe('change', function (e) {

                var url = element.value;

                self.updateResults(url);
            });
        });
    },

    getUrlParametersAsArray: function(url) {
        var position = url.indexOf('?');
        if (position == -1) {
            return [];
        }
        var parametersPart = url.substr(position + 1);
        return parametersPart.split('&');
    },
    
    getUrlBasePart: function(url) {
        var position = url.indexOf('?');
        if (position == -1) {
            return url;
        }
        return url.substring(0, position);
    },
    
    addParamToFilterUrls: function (parameterToAdd) {
        var self = this;
        $$('.block-layered-nav a').each(function (link) {
            self.toggleParameterInLink(parameterToAdd, link);
        });
    },

    removeParamFromFilterUrls: function (parameterToRemove) {
        var self = this;
        $$('.block-layered-nav a').each(function (link) {
            self.toggleParameterInLink(parameterToRemove, link, true);
        });
    },

    replaceParam: function (oldUrl, oldParameter, newParameter) {
        var newUrl;
        if (!newParameter.length) {
            newUrl = oldUrl
                .replace('&' + oldParameter + '&', '&')
                .replace('?' + oldParameter + '&', '?');
            if (newUrl == oldUrl) {
                newUrl = oldUrl
                    .replace('&' + oldParameter, '')
                    .replace('?' + oldParameter, '');
            }
        } else {
            newUrl = oldUrl
                .replace('&' + oldParameter + '&', '&' + newParameter + '&')
                .replace('?' + oldParameter + '&', '?' + newParameter + '&');
            if (newUrl == oldUrl) {
                newUrl = oldUrl
                    .replace('&' + oldParameter, '&' + newParameter)
                    .replace('?' + oldParameter, '?' + newParameter);
            }
        }
        return newUrl

    },

    toggleParameterInLink: function (parameterToToggle, link, removeOnly) {
        var parameterToToggleParts = parameterToToggle.split('=');
        var parameterToToggleKey = parameterToToggleParts[0];
        var parameterToToggleValue = parameterToToggleParts[1].toString();

        var linkUrl = link.href;
        var linkParams = this.getUrlParametersAsArray(linkUrl);

        if (linkUrl.indexOf('&' + parameterToToggleKey + '=') != -1 || linkUrl.indexOf('?' + parameterToToggleKey + '=') != -1) {
            for (var index = 0; index < linkParams.length; index++) {
                var linkParameter = linkParams[index];
                var linkParameterParts = linkParameter.split('=');
                var linkParameterKey = linkParameterParts[0];
                var linkParameterValue = linkParameterParts[1].toString();
                if (linkParameterKey == parameterToToggleKey) {
                    if (linkParameterValue == parameterToToggleValue) {
                        linkUrl = this.replaceParam(linkUrl, parameterToToggle, '');
                    } else {
                        var linkParameterValues = linkParameterValue.split(encodeURIComponent(','));
                        var parameterToToggleValues = parameterToToggleValue.split(encodeURIComponent(','));
                        for (var parameterToToggleIndex = 0; parameterToToggleIndex < parameterToToggleValues.length; parameterToToggleIndex++) {
                            var parameterToToggleSingleValue = parameterToToggleValues[parameterToToggleIndex];
                            if (linkParameterValues.indexOf(parameterToToggleSingleValue) != -1) {
                                delete linkParameterValues[linkParameterValues.indexOf(parameterToToggleSingleValue)];
                                linkParameterValues = linkParameterValues.filter(Number);
                            } else if (!removeOnly) {
                                linkParameterValues.push(parameterToToggleSingleValue);
                            }
                        }
                        
                        linkUrl = this.replaceParam(linkUrl, linkParameter, linkParameterKey + '=' + linkParameterValues.join(encodeURIComponent(',')));
                    }
                }
            }
        } else {
            if (linkParams.indexOf(parameterToToggle) != -1) {
                linkUrl = this.replaceParam(linkUrl, parameterToToggle, '');
            } else {
                if (linkUrl.indexOf('?') != -1) {
                    linkUrl = linkUrl + '&' + parameterToToggle;
                } else {
                    linkUrl = linkUrl + '?' + parameterToToggle;
                }
            }
        }
        link.href = linkUrl;
    },

    removeParamFromLink: function (parameterToRemove, link) {
        var parameterToRemoveParts = parameterToRemove.split('=');
        var parameterToRemoveKey = parameterToRemoveParts[0];
        var parameterToRemoveValue = parameterToRemoveParts[1];

        var linkUrl = link.href;
        var linkParams = this.getUrlParametersAsArray(linkUrl);

        if (linkUrl.indexOf('&' + parameterToRemoveKey + '=') != -1 || linkUrl.indexOf('?' + parameterToRemoveKey + '=') != -1) {
            for (var index = 0; index < linkParams.length; index++) {
                var linkParameter = linkParams[index];
                var linkParameterParts = linkParameter.split('=');
                var linkParameterKey = linkParameterParts[0];
                var linkParameterValue = linkParameterParts[1];
                if (linkParameterKey == parameterToRemoveKey) {
                    if (linkParameterValue == parameterToRemoveValue) {
                        linkUrl = this.replaceParam(linkUrl, parameterToRemove, '');
                    } else {
                        var linkParameterValues = linkParameterValue.split(encodeURIComponent(','));
                        if (linkParameterValues.indexOf(parameterToRemoveKey) != -1) {
                            delete linkParameterValues[linkParameterValues.indexOf(parameterToRemoveKey)];
                        } else {
                            linkParameterValues.push(parameterToRemoveValue);
                        }
                        linkUrl = this.replaceParam(linkUrl, linkParameter, linkParameterKey + '=' + linkParameterValues.join(encodeURIComponent(',')));
                    }
                }
            }
        }
        link.href = linkUrl;
    }
};