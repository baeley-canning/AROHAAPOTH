import React from 'react'
import { useAppContext } from '@/context/AppContext'

const Navbar = () => {

  const { router } = useAppContext()

  return (
    <div className='flex items-center px-4 md:px-8 py-3 justify-between border-b border-linen-100/80'>
      <button onClick={()=>router.push('/')} className='flex flex-col leading-tight text-left'>
        <span className='font-display text-lg text-ink-900'>Aroha</span>
        <span className='text-[10px] uppercase tracking-[0.3em] text-sage-700'>Apothecary</span>
      </button>
      <button className='bg-sage-600 text-linen-50 px-5 py-2 sm:px-7 sm:py-2 rounded-full text-xs sm:text-sm hover:bg-sage-700 transition'>Logout</button>
    </div>
  )
}

export default Navbar
