import { ref, computed } from 'vue'
import { useAuth } from './useAuth'

// Currency definitions
export const currencies = {
  PKR: { code: 'PKR', symbol: '₨', name: 'Pakistani Rupee', position: 'before' },
  INR: { code: 'INR', symbol: '₹', name: 'Indian Rupee', position: 'before' },
  USD: { code: 'USD', symbol: '$', name: 'US Dollar', position: 'before' },
  EUR: { code: 'EUR', symbol: '€', name: 'Euro', position: 'before' },
  GBP: { code: 'GBP', symbol: '£', name: 'British Pound', position: 'before' },
  AED: { code: 'AED', symbol: 'د.إ', name: 'UAE Dirham', position: 'before' },
  SAR: { code: 'SAR', symbol: '﷼', name: 'Saudi Riyal', position: 'before' },
  CAD: { code: 'CAD', symbol: 'C$', name: 'Canadian Dollar', position: 'before' },
  AUD: { code: 'AUD', symbol: 'A$', name: 'Australian Dollar', position: 'before' },
}

const currentCurrency = ref('PKR') // Default to PKR

export function useCurrency() {
  const { user } = useAuth()

  // Get currency from user or default to PKR
  const getCurrency = computed(() => {
    return user.value?.currency || currentCurrency.value || 'PKR'
  })

  // Get currency info
  const getCurrencyInfo = computed(() => {
    return currencies[getCurrency.value] || currencies.PKR
  })

  // Format amount with currency
  const formatCurrency = (amount) => {
    if (amount === null || amount === undefined || amount === '') {
      const currencyInfo = getCurrencyInfo.value
      return currencyInfo.position === 'before' ? `${currencyInfo.symbol}0.00` : `0.00 ${currencyInfo.symbol}`
    }
    
    const currencyInfo = getCurrencyInfo.value
    const numAmount = parseFloat(amount) || 0
    const formatted = numAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    
    if (currencyInfo.position === 'before') {
      return `${currencyInfo.symbol}${formatted}`
    } else {
      return `${formatted} ${currencyInfo.symbol}`
    }
  }

  // Update currency
  const updateCurrency = async (currencyCode) => {
    if (!currencies[currencyCode]) {
      console.error('Invalid currency code:', currencyCode)
      return
    }
    
    currentCurrency.value = currencyCode
    
    // Update user currency in backend
    try {
      const response = await axios.put('/user/currency', { currency: currencyCode })
      if (response.data.user) {
        user.value = response.data.user
      }
      return { success: true }
    } catch (error) {
      console.error('Failed to update currency:', error)
      return { success: false, error: error.response?.data?.message || 'Failed to update currency' }
    }
  }

  return {
    currencies,
    currentCurrency: getCurrency,
    currencyInfo: getCurrencyInfo,
    formatCurrency,
    updateCurrency,
  }
}

